<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

use App\Core\Url;
use App\Core\Csrf;
require __DIR__ . '/../layout/header.php';

$accounts = $view->bankAccounts;
?>

<div class="page-header">
    <div>
        <h1>Bankovní údaje</h1>
        <p>Bankovní účty vaší firmy.</p>
    </div>

    <div class="actions">
        <a
            href="<?= Url::to('/{tenant}/system/company/bank-accounts/create/#main') ?>"
            class="btn btn-primary"
            title="Přidat bankovní účet"
        >
            ➕ Přidat bankovní účet
        </a>
    </div>
</div>

<?php if ($accounts === []): ?>

    <p>
        Zatím není zadán žádný bankovní účet.
    </p>

<?php else: ?>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th scope="col">Název účtu</th>
                    <th scope="col">Číslo účtu</th>
                    <th scope="col">IBAN</th>
                    <th scope="col">BIC</th>
                    <th scope="col">Stav</th>
                    <th scope="col">Výchozí</th>
                    <th scope="col">Akce</th>
                </tr>
            </thead>

            <tbody>
                <?php foreach ($accounts as $account): ?>

                    <?php
                    $active = (int)$account['active'] === 1;
                    $default = (int)$account['is_default'] === 1;

                    $accountNumber = $account['account_number'];

                    if (
                        $account['account_prefix'] !== null
                        && $account['account_prefix'] !== ''
                    ) {
                        $accountNumber =
                            $account['account_prefix']
                            . '-'
                            . $account['account_number'];
                    }

                    if (
                        $account['bank_code'] !== null
                        && $account['bank_code'] !== ''
                    ) {
                        $accountNumber .=
                            ' / '
                            . $account['bank_code'];
                    }
                    ?>

                    <tr>
                        <td>
                            <?= e($account['name']) ?>
                        </td>

                        <td>
                            <?= e($accountNumber) ?>
                        </td>

                        <td>
                            <?= e(
                                $account['iban'] !== null
                                    && $account['iban'] !== ''
                                    ? $account['iban']
                                    : '-'
                            ) ?>
                        </td>

                        <td>
                            <?= e(
                                $account['bic'] !== null
                                    && $account['bic'] !== ''
                                    ? $account['bic']
                                    : '-'
                            ) ?>
                        </td>

                        <td>
                            <?php if ($active): ?>
                                <span class="status-active">
                                    Aktivní
                                </span>
                            <?php else: ?>
                                <span class="status-inactive">
                                    Neaktivní
                                </span>
                            <?php endif; ?>
                        </td>

                        <td>
                            <?php if ($default): ?>
                                <strong>⭐ Ano</strong>
                            <?php else: ?>
                                -
                            <?php endif; ?>
                        </td>

                        <td>
                            <div class="actions">

                                <a
                                    href="<?= Url::to(
                                        '/{tenant}/system/company/bank/'
                                        . $account['id']
                                        . '/edit/#main'
                                    ) ?>"
                                    class="btn btn-secondary"
                                    title="Upravit bankovní účet"
                                >
                                    ✏️ Upravit
                                </a>

                                <?php if ($active): ?>

                                    <?php if (!$default): ?>
                                        <form
                                            method="post"
                                            action="<?= Url::to('/{tenant}/system/company/bank-accounts/' . $account['id'] . '/set-default') ?>"
                                            class="inline-form"
                                        >
                                            <?= Csrf::getField() ?>

                                            <button
                                                type="submit"
                                                class="btn btn-secondary"
                                                title="Nastavit účet jako výchozí"
                                            >
                                                ⭐ Nastavit jako výchozí
                                            </button>
                                        </form>
                                    <?php endif; ?>

                                    <form
                                        method="post"
                                        action="<?= Url::to('/{tenant}/system/company/bank-accounts/' . $account['id'] . '/deactivate') ?>"
                                        class="inline-form"
                                    >
                                        <?= Csrf::getField() ?>

                                        <button
                                            type="submit"
                                            class="btn btn-secondary"
                                            title="Deaktivovat bankovní účet"
                                        >
                                            Deaktivovat
                                        </button>
                                    </form>

                                <?php else: ?>

                                    <form
                                        method="post"
                                        action="<?= Url::to('/{tenant}/system/company/bank-accounts/' . $account['id'] . '/activate') ?>"
                                        class="inline-form"
                                    >
                                        <?= Csrf::getField() ?>

                                        <button
                                            type="submit"
                                            class="btn btn-secondary"
                                            title="Aktivovat bankovní účet"
                                        >
                                            Aktivovat
                                        </button>
                                    </form>

                                <?php endif; ?>

                            </div>
                        </td>
                    </tr>

                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

<?php endif; ?>

<?php require __DIR__ . '/../layout/footer.php'; ?>