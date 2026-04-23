        </div><!-- /content-area -->
    </main><!-- /main-content -->
</div><!-- /app-layout -->

<!-- Toast notifications -->
<div class="toast-container" id="toastContainer"></div>

<!-- Overlay for mobile sidebar -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<script src="<?= APP_URL ?>/public/js/main.js"></script>
<?php if (isset($extra_js)): ?>
<script src="<?= APP_URL ?>/public/js/<?= $extra_js ?>.js"></script>
<?php endif; ?>
<?php if (isset($inline_js)): ?>
<script><?= $inline_js ?></script>
<?php endif; ?>
</body>
</html>
