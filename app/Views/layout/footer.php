<?php

use \App\Core\Auth;
use \App\Core\Url;
use \App\Core\Config;
?>
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
            <a href="<?= Url::to('/pages/terms') ?>" class="link">Podmínky použití</a>

            <a href="<?= Url::to('/pages/privacy') ?>" class="link">Ochrana osobních údajů</a>

            <a href="<?= Url::to('/pages/cookies') ?>" class="link">Cookies</a>
        </div>

        <!-- RIGHT -->
        <div class="footer-right">
            <?php if (Auth::check()): ?>
                <small>
                    <?= e(Auth::company() ?? '') ?>
                </small><br>
                <small>verze: <?= e(Config::get('app.version') ?? 'dev') ?></small>
            <?php else: ?>
                <a href="<?= Url::to('/login') ?>">Přihlášení</a>
            <?php endif; ?>
        </div>

    </div>
</footer>

</div>
</body>
</html>