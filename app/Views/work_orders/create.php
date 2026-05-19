<?php
declare(strict_types=1);

/** @var \App\Core\ViewContext $view */

require __DIR__ . '/../layout/header.php';

use App\Core\Url;
use App\Core\Csrf;
use App\Core\Access;
$data = $view->data;
$teams = $view->teams; 
?>

<div class="create-container">

    <!-- 🔹 FORM -->
    <div class="card">
        <div class="card-body">

            <?php require __DIR__ . '/../layout/formsErrors.php'; ?>

            <form method="post">
                <?= Csrf::getField() ?>

                <!-- Externí číslo -->
                <div class="form-group">
                    <label for="external_number">Externí číslo</label>
                    <input
                        class="form-control"
                        name="external_number"
                        id="external_number"
                        value="<?= e($data['external_number'] ?? '') ?>"
                    >
                </div>
                <div class="form-group">
                    <label for="internal_number">Interní číslo</label>
                    <?php if(!isset($data['internal_number'])): ?>
                    Interní číslo zakázky bude vytvořeno až při uložení zakázky.
                    <?php else: ?>
                    <?= e($data['internal_number']) ?>
                    <?php endif; ?>
                </div>
                <!-- Název -->
                <div class="form-group">
                    <label for="title">Název <span class="req">*</span></label>
                    <input
                        class="form-control"
                        name="title"
                        id="title"
                        required
                        value="<?= e($data['title'] ?? '') ?>"
                    >
                </div>

                <!-- Popis -->
                <div class="form-group">
                    <label for="description">Popis</label>
                    <textarea
                        class="form-control"
                        name="description"
                        id="description"
                        rows="4"
                    ><?= e($data['description'] ?? '') ?></textarea>
                </div>
                <!-- Termín dokončení -->
                <div class="form-group">
                    <label for="wo_due_date">Termín dokončení</label>
                    <input
                        type="date"
                        class="form-control"
                        name="wo_due_date"
                        id="wo_due_date"
                        value="<?= e($data['wo_due_date'] ?? '') ?>"
                    >
                </div>
                 <!-- Předpokládané hodiny -->
                <div class="form-group">
                    <label for="estimated_hours">Předpokládaný počet hodin k dokončení zakázky</label>
                    <input
                        type="number"
                        class="form-control"
                        name="estimated_hours"
                        id="estimated_hours"
                        value="<?= e($data['estimated_hours'] ?? '') ?>"
                    >
                </div>
               
               <!-- Zdroj -->
                <div class="form-group">
                    <label for="source">Zdroj</label>
                    <select class="form-control" name="source" id="source">
                        <option value="email" <?= ($data['source'] ?? '') === 'email' ? 'selected' : '' ?>><?= te('email') ?></option>
                        <option value="phone" <?= ($data['source'] ?? '') === 'phone' ? 'selected' : '' ?>><?= te('phone') ?></option>
                        <option value="personal" <?= ($data['source'] ?? '') === 'personal' ? 'selected' : '' ?>><?= te('personal') ?></option>
                        <option value="system" <?= ($data['source'] ?? '') === 'system' ? 'selected' : '' ?>><?= te('system') ?></option>
                    </select>
                </div>

                <!-- Požadoval -->
                <div class="form-group">
                    <label for="requested_by">Požadoval</label>
                    <input
                        class="form-control"
                        name="requested_by"
                        id="requested_by"
                        value="<?= e($data['requested_by'] ?? '') ?>"
                    >
                </div>

                <!-- Kontakt -->
                <!-- Kontaktní osoba -->
                <div class="form-group">
                    <label for="contact_person">Kontaktní osoba</label>
                    <input
                        class="form-control"
                        name="contact_person"
                        id="contact_person"
                        value="<?= e($data['contact_person'] ?? '') ?>"
                    >
                </div>
                <!-- Priorita -->
                <div class="form-group">
                    <label for="priority">Priorita</label>
                    <select class="form-control" name="priority" id="priority">
                        <option value="low" <?= ($data['priority'] ?? '') === 'low' ? 'selected' : '' ?>>Nízká</option>
                        <option value="normal" <?= ($data['priority'] ?? '') === 'normal' ? 'selected' : '' ?>>Normální</option>
                        <option value="high" <?= ($data['priority'] ?? '') === 'high' ? 'selected' : '' ?>>Vysoká</option>
                        <option value="emergency" <?= ($data['priority'] ?? '') === 'emergency' ? 'selected' : '' ?>>Havárie</option>
                    </select>
                </div>

            <!-- Fakturace -->
            
                <div class="section-label">Zákazník: </div>

                <div class="form-group">
                    <label for="contact_id">Odběratel: </label>

                    <select name="contact_id" id="contact_id" class="form-control">

                        <option value="">— bez zákazníka —</option>
                        <?php foreach ($view->contacts ?? [] as $c): ?>
                            <option value="<?= $c['id'] ?>"
                                <?= (($data['contact_id'] ?? null) == $c['id']) ? 'selected' : '' ?>
                            >
                                <?= e($c['company_name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                </div>

                <div class="form-group">
                    <div class="section-label">Cenové sazby: </div>
                    <label for="price_per_hour">Cena za hodinu (Kč)</label>
                <input type="number"
                    step="1"
                    min="0"
                    name="price_per_hour"
                    id="price_per_hour"
                    class="form-control"
                    value="<?= e($data['price_per_hour'] ?? 0) ?>"
                    placeholder="0">

                </div>

                <div class="form-group">
                    <label for="price_per_km">Cena za km (Kč)</label>
                    <input type="number"
                        step="1"
                        min="0"
                        name="price_per_km"
                        id="price_per_km"
                        class="form-control"
                        value="<?= e($data['price_per_km'] ?? 0) ?>"
                        placeholder="0">
                </div>
                    

                <div class="form-group">
                    <label>Úkol ze zakázky</label>
                    <?php if(isset($data['is_edit']) && ($data['is_edit'] === true)
                            && (($data['count_tasks'] ?? 0) === 0)
                    ): ?>
                        <label for="create_task"><input 
                            type="checkbox" 
                            name="create_first_task"
                            id="create_task" 
                            value="1" <?= checked($data['create_first_task'] ?? false) ?>>
                        Automaticky vytvořit první úkol ze zakázky</label>
                    <?php else: ?>
                        <label for="create_task">Pokud již existuje k 
                            zakázce nějaký úkol, tak nelze ze zakázky 
                            vytvořit automaticky úkol.</label>
                    <?php endif; ?>
                </div>

                <div class="form-group">
                    <label for="team_id">Tým</label>
                    <select name="team_id"
                            id="team_id" 
                            class="form-control"
                            >
                            <?php foreach ($teams as $team): ?>
                            <option 
                                value="<?= $team['id'] ?>"
                                <?= (($data['team_id'] ?? null) == $team['id']) ? 'selected' : '' ?>
                                >
                                <?= e($team['name']) ?>
                            </option>
                        <?php endforeach; ?>

                    </select>
                   <small class="form-text text-muted">
                      Vyberte tým, který bude mít úkol na starosti.<br>
                      
                    </small>
                </div>

                <!-- ACTIONS -->
                <div class="form-actions">
                    <button class="btn btn-primary">Uložit</button>
                    <a href="<?= Url::to('/{tenant}/work-orders') ?>/#main" class="btn btn-secondary">
                        Zpět na přehled
                    </a>
                </div>

            </form>

        </div>
    </div>

    <!-- 🔹 HELP (klidně později) -->
    <div class="card card-help">
        <div class="card-body">
            <h3>Nápověda</h3>
             <h4>Tip</h4>
            <p>
               Vyplňte co nejvíce informací - usnadní to zpracování požadavku.
            </p>
             <h4>Externí číslo</h4>
            <p>
                Externí číslo je číslo, které používá zákazník. Může to být například číslo z jeho systému
                 nebo číslo z objednávky.
            </p>
            <h4>Interní číslo</h4>
            <p>
                Interní číslo je číslo, které používáme my. 
                Je to unikátní identifikátor zakázky, který nám 
                pomáhá ji rychle najít a odlišit od ostatních 
                zakázek.
                Je základem pro generování faktury a dalších dokumentů. Je vygenerováno systémem a 
                nelze ho měnit, protože je základem pro všechny další procesy, které s zakázkou souvisí.
            </p>
            <h4>Název zakázky</h4>
            <p>
                Je lidsky přívětivý identifikátor zakázky, který nám 
                pomáhá ji rychle najít a odlišit od ostatních zakázek.
                A je povinnou položkou, protože bez názvu nám zakázka 
                nedává smysl.
            </p>
             <h4>Popis zakázky</h4>
            <p>
                Je to místo, kam můžete napsat 
                podrobnosti o zakázce, které nejsou 
                zřejmé z názvu. 
                Můžete zde uvést například rozsah 
                prací, specifické požadavky zákazníka, 
                nebo cokoliv dalšího, co považujete 
                za důležité. Toto pole není povinné,
                ale může být velmi užitečné pro všechny,
                kdo budou s zakázkou pracovat a vytvářet úkoly.
             </p>
             <h4>Termín dokončení</h4>
            <p>
                Termín dokončení je datum, do kterého má být zakázka dokončena.
            </p>
            
            <h4>Předpokládané hodiny</h4>
            <p>Předpokládaný nebo jasně určený čas potřebný k dokončení zakázky. 
                Tato informace nám pomáhá lépe plánovat a odhadovat, 
                kolik práce bude potřeba k dokončení zakázky.
            </p>
            <h4>Zdroj</h4>
            <p>
                Zdroj zakázky je informace o tom, jak 
                se k nám zakázka dostala. Může to být 
                například e-mail, telefon, nebo 
                oslovování zákazníka.
            </p>
            <h4>Požadoval</h4>
            <p> 
                Je informace o tom, kdo zakázku 
                požadoval. Může to být jméno osoby, 
                nebo název oddělení, které zakázku 
                požadovalo. Důležité u servisních firem, 
                většinou to bude zákazník, ale může to 
                být i interní požadavek od jiného oddělení.

            </p>
            <h4>Priorita</h4>
            <p>
                Priorita zakázky je informace o tom, 
                jak důležitá je pro vás nebo pro klienta. 
                Může to být "havárie", "vysoká", "střední" 
                nebo "nízká".
            </p>
           <h4>Zákazník</h4>
            <p>
                Zákazník je osoba nebo organizace, 
                která požadovala zakázku. 
                Zákazníky můžete přidávat v sekci 
                <a href="<?= Url::to('/{tenant}/contacts') ?>" class="link">Kontakty</a>.
            </p>
            <h4>Cenové sazby</h4>
            <p>
                Cenové sazby jsou informace o tom, jaké ceny 
                budete účtovat za práci na této zakázce. 
                Můžete zde uvést cenu za hodinu práce, nebo 
                cenu za kilometr, pokud se jedná o zakázku, 
                která zahrnuje cestování.
            </p>
            
            <h4>Automaticky vytvořit první úkol ze zakázky</h4>
            <p>
                Je to způsob jak automaticky vytvořit první úkol, 
                který bude Je to prosté zkopírování názvu zakázky 
                a jejího popisu do úkolu, který bude mít na 
                starosti tým, který vyberete níže.

            </p>
            
            <h4>Tým</h4>
            <p>
                Tým je skupina lidí, která bude pracovat na této 
                zakázce. 
                Musíte zde vybrat tým, který bude mít na starosti 
                zpracování zakázky a zodpovědnost za její dokončení.
            </p>
     </div>
    </div>

</div>


<?php require __DIR__ . '/../layout/footer.php';

