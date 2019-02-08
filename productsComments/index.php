<?php
    echo '<p>import de comentários dos produtos<p><hr />';

    $options = [
        'see_origin_data',
        'see_final_data',
        'generate_export_csv',
        'emulate_import',
        'import_csv',
        'see_file_to_export',
        'validate_file_to_export',
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