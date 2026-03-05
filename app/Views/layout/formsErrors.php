<!-- Zobrazení chyb -->
<?php if (!empty($errors)): ?>
   <div class="ui-alert ui-alert-danger">
       <ul style="margin:0;">
           <?php foreach ($errors as $field => $error): ?>
               <?php if (is_array($error)): ?>
                   <?php foreach ($error as $message): ?>
                       <li><?= e($message) ?></li>
                   <?php endforeach; ?>
               <?php else: ?>
                   <li><?= e($error) ?></li>
               <?php endif; ?>
           <?php endforeach; ?>
       </ul>
   </div>
<?php endif; ?>
