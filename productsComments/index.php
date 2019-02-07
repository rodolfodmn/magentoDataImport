<?php
    echo '<p>import de comentários dos produtos<p><hr />';

    $options = [
        'see_origin_data',
        'see_final_data',
        'export_csv',
        'emulate_import',
        'import_csv',
        'validate_import'
    ];

    echo "<p>O que você precisa hoje?<p><br />";
    echo '<p>Informe:</p>';
    echo "<ul>";
    foreach ($options as $key => $value) {
        echo "<li> ".($key+1)." - para: $value</li>";
    }
    echo "</ul>";
    ?>
<form action="./actions.php">
    <input type="text" name="option">
    <button type="submit" method='get'>enviar</button>
</form>