    </main>

    <?php if (isset($_SESSION['user_id'])): ?>
    <footer class="footer">
        <div class="footer-content">
            <p>&copy; <?= date('Y') ?> <?= APP_NAME ?> - Tous droits réservés</p>
            <p>Version <?= APP_VERSION ?></p>
        </div>
    </footer>
    <?php endif; ?>

    <!-- JavaScript -->
    <script src="<?= APP_URL ?>/public/js/app.js"></script>
</body>
</html>
