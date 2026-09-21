<?php
// app/Config/roles_in_team.php
/** jakou roli si muže vybrat klient pro zaměstnance při vytváření týmů...
 * 
 */
return [
    'default' => 'member',

    'roles' => [
        // 'owner'  => 'Vlastník',
        'leader' => 'Vedoucí',
        'member' => 'Člen',
        'guest'  => 'Host',
    ],
];
