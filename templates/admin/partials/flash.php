<?php declare(strict_types=1); ?>
<?php $success = App\Core\Session::getFlash('success'); ?>
<?php $error = App\Core\Session::getFlash('error'); ?>
<?php if (is_string($success) && $success !== ''): ?><p class="flash flash-success"><?= e($success) ?></p><?php endif; ?>
<?php if (is_string($error) && $error !== ''): ?><p class="flash flash-error"><?= e($error) ?></p><?php endif; ?>
