<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class AIController extends Controller
{
    /**
     * Ask AI about products, orders, categories
     */
    public function ask(Request $request)
    {
        $request->validate([
            'question' => 'required|string|max:500'
        ]);

        $question = $request->question;
        $userId = auth()->id();

        try {
            // Fetch context data
            $context = $this->fetchContext($userId, $question);
            
            // Check if we have Google AI API key
            $apiKey = env('GOOGLE_AI_KEY');
            
            if ($apiKey) {
                // Use Google Gemini AI
                $response = $this->askGeminiAI($question, $context, $apiKey);
            } else {
                // Use smart fallback
                $response = $this->smartFallback($question, $context);
            }

            return response()->json([
                'success' => true,
                'question' => $question,
                'answer' => $response['answer'],
                'sources' => $response['sources'] ?? []
            ]);

        } catch (\Exception $e) {
            \Log::error('AI Error: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'question' => $question,
                'answer' => '❌ Xin lỗi, tôi gặp lỗi khi xử lý câu hỏi. Vui lòng thử lại sau.'
            ], 500);
        }
    }

    /**
     * Fetch context data based on question
     */
    private function fetchContext($userId, $question)
    {
        $context = [];
        $q = mb_strtolower($question);

        // Current user info
        if ($userId) {
            $context['currentUser'] = User::find($userId);
        }

        // Products context
        if (preg_match('/(sản phẩm|vợt|giày|áo|quần|túi|cầu|product)/ui', $question)) {
            $context['products'] = Product::with('category')
                ->active()
                ->orderBy('created_at', 'DESC')
                ->limit(50)
                ->get();
            
            $context['categories'] = Category::active()->get();
        }

        // Orders context
        if (preg_match('/(đơn hàng|order|mua|đặt)/ui', $question)) {
            if ($userId) {
                $context['userOrders'] = Order::where('user_id', $userId)
                    ->with('orderItems.product')
                    ->orderBy('created_at', 'DESC')
                    ->limit(20)
                    ->get();
            }
            
            // Admin can see all orders
            if ($userId && User::find($userId)->isAdmin()) {
                $context['allOrders'] = Order::with(['user', 'orderItems.product'])
                    ->orderBy('created_at', 'DESC')
                    ->limit(50)
                    ->get();
            }
        }

        // Categories context
        if (preg_match('/(danh mục|category|loại)/ui', $question)) {
            $context['categories'] = Category::withCount('products')
                ->active()
                ->get();
        }

        // Stats context
        if (preg_match('/(thống kê|bao nhiêu|đếm|tổng|count)/ui', $question)) {
            $context['stats'] = [
                'total_products' => Product::active()->count(),
                'total_categories' => Category::active()->count(),
                'total_orders' => $userId ? Order::where('user_id', $userId)->count() : Order::count(),
                'pending_orders' => $userId ? Order::where('user_id', $userId)->where('status', 'pending')->count() : Order::where('status', 'pending')->count(),
            ];
        }

        return $context;
    }

    /**
     * Ask Gemini AI
     */
    private function askGeminiAI($question, $context, $apiKey)
    {
        $prompt = $this->buildPrompt($question, $context);

        try {
            $response = Http::timeout(30)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash-exp:generateContent?key={$apiKey}", [
                    'contents' => [
                        [
                            'parts' => [
                                ['text' => $prompt]
                            ]
                        ]
                    ],
                    'generationConfig' => [
                        'temperature' => 0.7,
                        'maxOutputTokens' => 2048,
                    ]
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $answer = $data['candidates'][0]['content']['parts'][0]['text'] ?? 'Không có câu trả lời.';
                
                return [
                    'answer' => $answer,
                    'sources' => $this->extractSources($context)
                ];
            }

            // Fallback if API fails
            return $this->smartFallback($question, $context);

        } catch (\Exception $e) {
            \Log::error('Gemini API Error: ' . $e->getMessage());
            return $this->smartFallback($question, $context);
        }
    }

    /**
     * Build AI prompt
     */
    private function buildPrompt($question, $context)
    {
        $today = date('d/m/Y');
        $prompt = "Bạn là trợ lý AI cho cửa hàng Badminton Shop. Hôm nay là {$today}.\n\n";

        // Current user
        if (isset($context['currentUser'])) {
            $user = $context['currentUser'];
            $prompt .= "NGƯỜI DÙNG: {$user->name} (Email: {$user->email})\n\n";
        }

        // Products
        if (isset($context['products']) && $context['products']->count() > 0) {
            $prompt .= "SẢN PHẨM ({$context['products']->count()}):\n";
            foreach ($context['products']->take(20) as $i => $p) {
                $price = $p->sale_price ?? $p->price;
                $cat = $p->category->name ?? 'N/A';
                $prompt .= ($i+1) . ". {$p->name} | {$cat} | " . number_format($price) . "₫ | Kho: {$p->quantity}\n";
            }
            $prompt .= "\n";
        }

        // Categories
        if (isset($context['categories']) && $context['categories']->count() > 0) {
            $prompt .= "DANH MỤC ({$context['categories']->count()}):\n";
            foreach ($context['categories'] as $i => $c) {
                $count = $c->products_count ?? $c->products()->count();
                $prompt .= ($i+1) . ". {$c->name} | {$count} sản phẩm\n";
            }
            $prompt .= "\n";
        }

        // Orders
        if (isset($context['userOrders']) && $context['userOrders']->count() > 0) {
            $prompt .= "ĐỠN HÀNG CỦA BẠN ({$context['userOrders']->count()}):\n";
            foreach ($context['userOrders']->take(10) as $i => $o) {
                $total = number_format($o->total_amount);
                $date = $o->created_at->format('d/m/Y');
                $prompt .= ($i+1) . ". #{$o->order_number} | {$date} | {$o->status} | {$total}₫\n";
            }
            $prompt .= "\n";
        }

        // Stats
        if (isset($context['stats'])) {
            $s = $context['stats'];
            $prompt .= "THỐNG KÊ:\n";
            $prompt .= "- Tổng sản phẩm: {$s['total_products']}\n";
            $prompt .= "- Tổng danh mục: {$s['total_categories']}\n";
            $prompt .= "- Đơn hàng: {$s['total_orders']}\n";
            $prompt .= "- Đơn chờ xử lý: {$s['pending_orders']}\n\n";
        }

        $prompt .= "HƯỚNG DẪN:\n";
        $prompt .= "1. Trả lời chính xác dựa trên dữ liệu trên\n";
        $prompt .= "2. Format markdown với emoji đẹp\n";
        $prompt .= "3. Nếu hỏi \"tôi/mình\" → tìm theo thông tin người dùng\n";
        $prompt .= "4. Nếu hỏi về giá → format số với dấu phẩy\n";
        $prompt .= "5. Đề xuất sản phẩm nếu phù hợp\n\n";
        
        $prompt .= "CÂU HỎI: {$question}\n\nTRẢ LỜI:";

        return $prompt;
    }

    /**
     * Smart fallback without AI
     */
    private function smartFallback($question, $context)
    {
        $q = mb_strtolower($question);
        $answer = '';

        // Count questions
        if (preg_match('/(bao nhiêu|đếm|tổng)/ui', $question)) {
            $answer = "📊 **THỐNG KÊ:**\n\n";
            
            if (isset($context['stats'])) {
                $s = $context['stats'];
                $answer .= "📦 **Sản phẩm:** " . $s['total_products'] . "\n";
                $answer .= "📁 **Danh mục:** " . $s['total_categories'] . "\n";
                $answer .= "🛒 **Đơn hàng:** " . $s['total_orders'] . "\n";
                $answer .= "⏳ **Chờ xử lý:** " . $s['pending_orders'] . "\n";
            }
        }
        // Product list questions
        elseif (preg_match('/(sản phẩm|vợt|giày)/ui', $question) && isset($context['products'])) {
            $answer = "🏸 **SẢN PHẨM HIỆN CÓ:**\n\n";
            foreach ($context['products']->take(10) as $p) {
                $price = number_format($p->sale_price ?? $p->price);
                $answer .= "• **{$p->name}**\n  💰 {$price}₫ | 📦 Kho: {$p->quantity}\n\n";
            }
        }
        // Order questions
        elseif (preg_match('/(đơn hàng|order)/ui', $question) && isset($context['userOrders'])) {
            $answer = "🛒 **ĐƠN HÀNG CỦA BẠN:**\n\n";
            foreach ($context['userOrders']->take(5) as $o) {
                $total = number_format($o->total_amount);
                $answer .= "• **#{$o->order_number}**\n";
                $answer .= "  📅 {$o->created_at->format('d/m/Y')} | 💰 {$total}₫ | ";
                $answer .= "📊 " . ucfirst($o->status) . "\n\n";
            }
        }
        // Default
        else {
            $answer = "👋 Xin chào! Tôi là trợ lý AI của Badminton Shop.\n\n";
            $answer .= "Bạn có thể hỏi tôi về:\n";
            $answer .= "• Sản phẩm và giá cả\n";
            $answer .= "• Đơn hàng của bạn\n";
            $answer .= "• Danh mục sản phẩm\n";
            $answer .= "• Thống kê cửa hàng\n\n";
            $answer .= "Hãy thử hỏi: \"Có bao nhiêu sản phẩm?\" hoặc \"Đơn hàng của tôi thế nào?\"";
        }

        return [
            'answer' => $answer,
            'sources' => $this->extractSources($context)
        ];
    }

    /**
     * Extract sources from context
     */
    private function extractSources($context)
    {
        $sources = [];
        
        if (isset($context['products'])) {
            $sources[] = $context['products']->count() . ' sản phẩm';
        }
        if (isset($context['categories'])) {
            $sources[] = $context['categories']->count() . ' danh mục';
        }
        if (isset($context['userOrders'])) {
            $sources[] = $context['userOrders']->count() . ' đơn hàng';
        }
        
        return $sources;
    }
}
