# Product Images Storage Guide

## Directory Structure
```
storage/app/public/products/
├── rackets/           # Badminton rackets
├── shoes/            # Badminton shoes  
├── apparel/          # Clothing & apparel
├── accessories/      # Bags, grips, strings, etc.
└── shuttlecocks/     # Shuttlecocks
```

## Image Requirements
- **Format:** JPG, PNG, WebP (preferred)
- **Size:** Maximum 2MB per image
- **Dimensions:** Recommended 800x800px for product main images
- **Naming:** Use descriptive names (e.g., `yonex-arcsaber-11-red.jpg`)

## How to Add Images

### 1. Via Admin Panel (when implemented)
- Upload through the product creation/edit form
- Images will be automatically resized and optimized

### 2. Manual Upload
1. Copy images to the appropriate subdirectory
2. Update the database product record with the image path
3. Example: `products/rackets/yonex-arcsaber-11.jpg`

### 3. Via Seeder (for bulk import)
- Use the ProductSeeder to import products with images
- Place images in the storage directory first
- Reference them in the seeder file

## Image URLs in Application
Images are accessible via: `asset('storage/products/category/image-name.jpg')`

Example: `asset('storage/products/rackets/yonex-arcsaber-11.jpg')`
