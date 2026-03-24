</main>

<footer class="footer">
    <div class="footer-inner">

        <!-- LEFT -->
        <div class="footer-left">
            <strong>Bó</strong> – servisní zápisník<br>
            <small>Vytvořil Petr Vaněk · 2025 - <?= date('Y') ?></small>
        </div>

        <!-- CENTER -->
        <div class="footer-center">
            <a href="<?= \App\Core\Url::to('/terms') ?>">Podmínky</a>
            ·
            <a href="<?= \App\Core\Url::to('/privacy') ?>">Ochrana údajů</a>
        </div>

        <!-- RIGHT -->
        <div class="footer-right">
            <?php if (\App\Core\Auth::check()): ?>
                <small>
                    <?= e(\App\Core\Auth::company() ?? '') ?>
                </small><br>
                <small>verze: <?= e(\App\Core\Config::get('app.version') ?? 'dev') ?></small>
            <?php else: ?>
                <a href="<?= \App\Core\Url::to('/login') ?>">Přihlášení</a>
            <?php endif; ?>
        </div>

    </div>
</footer>

</div>
</body>
</html>