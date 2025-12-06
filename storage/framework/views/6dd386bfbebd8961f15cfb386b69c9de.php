<?php if($paginator->hasPages()): ?>
    <nav aria-label="Page navigation" class="d-flex flex-column align-items-center">
        <ul class="pagination justify-content-center mb-0">
            
            <?php if($paginator->onFirstPage()): ?>
                <li class="page-item disabled" aria-disabled="true" aria-label="Previous">
                    <span class="page-link rounded-circle">
                        <i class="fas fa-chevron-left"></i>
                    </span>
                </li>
            <?php else: ?>
                <li class="page-item">
                    <a class="page-link rounded-circle" href="<?php echo e($paginator->previousPageUrl()); ?>" rel="prev" aria-label="Previous">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                </li>
            <?php endif; ?>

            
            <?php $__currentLoopData = $elements; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $element): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                
                <?php if(is_string($element)): ?>
                    <li class="page-item disabled" aria-disabled="true"><span class="page-link"><?php echo e($element); ?></span></li>
                <?php endif; ?>

                
                <?php if(is_array($element)): ?>
                    <?php $__currentLoopData = $element; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $page => $url): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <?php if($page == $paginator->currentPage()): ?>
                            <li class="page-item active" aria-current="page"><span class="page-link"><?php echo e($page); ?></span></li>
                        <?php elseif($page == $paginator->currentPage() - 1 || $page == $paginator->currentPage() + 1 || $page == 1 || $page == $paginator->lastPage()): ?>
                            
                            <li class="page-item"><a class="page-link" href="<?php echo e($url); ?>"><?php echo e($page); ?></a></li>
                        <?php elseif($page == 2 && $paginator->currentPage() > 4): ?>
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        <?php elseif($page == $paginator->lastPage() - 1 && $paginator->currentPage() < $paginator->lastPage() - 3): ?>
                            <li class="page-item disabled"><span class="page-link">&hellip;</span></li>
                        <?php endif; ?>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                <?php endif; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

            
            <?php if($paginator->hasMorePages()): ?>
                <li class="page-item">
                    <a class="page-link rounded-circle" href="<?php echo e($paginator->nextPageUrl()); ?>" rel="next" aria-label="Next">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php else: ?>
                <li class="page-item disabled" aria-disabled="true" aria-label="Next">
                    <span class="page-link rounded-circle">
                        <i class="fas fa-chevron-right"></i>
                    </span>
                </li>
            <?php endif; ?>
        </ul>

        
        <div class="text-center mt-3">
            <small class="text-muted">
                Hiển thị <strong><?php echo e($paginator->firstItem()); ?></strong> đến <strong><?php echo e($paginator->lastItem()); ?></strong>
                trong tổng số <strong><?php echo e($paginator->total()); ?></strong> kết quả
            </small>
        </div>
    </nav>
<?php endif; ?>
<?php /**PATH C:\Users\GIGABYTE\Desktop\BadmintonShop\resources\views/vendor/pagination/bootstrap-5.blade.php ENDPATH**/ ?>