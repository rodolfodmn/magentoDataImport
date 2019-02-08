<?php
function validateOption($option = null)
{

    if (empty($option)) {
        echo "<p>Não identifiquei a opção ¯\_(ツ)_/¯</p> </br>";
        exit;
    }
    switch ($option) {
        case '1':
            see_origin_data();
            break;
        case '2':
            break;
        case '3':
            generate_export_csv();
            break;
        case '4':
            break;
        case '5':
            break;
        case '6':
            see_file_to_export();
            break;
        case '7':
            validate_file_to_export();
            break;
        case '8':
            see_file_to_export(true);
            break;
        default:
            break;
    }
}

function select_products()
{
    $collection = mage::getModel('catalog/product')->getCollection();
    $select = $collection->getSelect();
    $select->reset(Zend_Db_Select::COLUMNS);
    $select->columns('entity_id');
    $select->columns('sku');
    $productResource = Mage::getModel('catalog/product')->getResource();

    return $collection;
}

function genarate_csv_by_array($arr = [], $name = 'file', $serialize = true)
{
    $file = fopen(Mage::getBaseDir('var') . "/$name.csv", "w+");
    foreach ($arr as $data) {
        fwrite($file, serialize($data) . "\n");
    }
    fclose($file);
}

function see_origin_data()
{
    echo '<p>Esses são os dados que serão importados:</p></hr>';

    $collection = select_products();

    $nulls = [];
    $count = 0;
    foreach ($collection as $key => $value) {
        $reviewCollection = Mage::getModel('review/review')->getCollection()
            ->addFieldToFilter('entity_pk_value', array("in" => array($key)));

        foreach ($reviewCollection as $review) {
            $count++;
            $data = $review->getData();
            foreach ($data as $k => $v) {
                if ($v == null) {
                    if (!isset($nulls[$k])) {
                        $nulls[$k] = 0;
                    }
                    $nulls[$k]++;
                }
            }
            $data['detail'] = trim($data['detail']);
            if ($data['customer_id']) {
                $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                if ($customer && $customer->getId()) {
                    $data['customer_id'] = $customer->getEmail();
                }
            }
            $data['sku'] = $value->getSku();
            var_dump($data);
        }
    }
    echo "<p>Serão importados $count comentários.</p>";
    foreach ($nulls as $fields => $count) {
        echo "<p>Existem $count $fields com null.</p>";
    }
}

function generate_export_csv()
{
    echo '<p>Iniciando Exportação de comentários<p></br>';

    $nulls = [];
    $count = 0;

    try {
        $collection = mage::getModel('catalog/product')->getCollection();
        $select = $collection->getSelect();
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('entity_id');
        $select->columns('sku');

        $productResource = Mage::getModel('catalog/product')->getResource();
        $file = fopen(Mage::getBaseDir('var') . '/review.csv', "w+");
        $read = Mage::getSingleton('core/resource')->getConnection('core_read');
        foreach ($collection as $key => $value) {
            $reviewCollection = Mage::getModel('review/review')->getCollection()
                ->addFieldToFilter('entity_pk_value', array("in" => array($key)));

            foreach ($reviewCollection as $review) {
                $count++;
                $data = $review->getData();
                foreach ($data as $k => $v) {
                    if ($v == null) {
                        if (!isset($nulls[$k])) {
                            $nulls[$k] = 0;
                        }
                        $nulls[$k]++;
                    }
                }
                $data['detail'] = trim($data['detail']);
                if ($data['customer_id']) {
                    $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                    if ($customer && $customer->getId()) {
                        $data['customer_id'] = $customer->getEmail();
                    }
                }
                $data['sku'] = $value->getSku();
                fwrite($file, serialize($data) . "\n");
            }
        }
        fclose($file);
        echo "<p>Serão importados $count comentários.</p>";
        foreach ($nulls as $fields => $count) {
            echo "<p>Existem $count $fields com null.</p>";
        }
        echo '<p>Finalizada a Exportação de comentários </p></br>';

        csv_data_count();
    } catch (\Exception $e) {
        var_dump($e->getMessage());
    }
}

function csv_data_count()
{
    echo '<p>Contegem dos intens para serem importados</p></br>';
    $file = fopen(Mage::getBaseDir('var') . '/review.csv', 'r');
    $count_import = 0;
    while (($line = fgets($file)) !== false) {
        try {
            if ($data = unserialize($line)) {
                $count_import += 1;
            }
        } catch (Exception $th) {
            var_dump($data);
            echo '<br />nem deu bom<br />';
            echo $th->getMessage();
        }
    }
    echo "<p>Serão importados $count_import comentários.</p>";
    echo 'Finalizada a validação da consistencia e contagem dos dados no csv</p></br>';
}

function see_file_to_export($diff_file = false)
{
    echo '<p>Visualização dos dados no arquivo de importação</p>';
    $name = ($diff_file) ? 'review_diff' : 'review';

    $file = fopen(Mage::getBaseDir('var') . "/$name.csv", 'r');
    $count_import = 0;
    while (($line = fgets($file)) !== false) {
        try {
            if ($data = unserialize($line)) {
                $count_import += 1;

                var_dump($data);
            }
        } catch (Exception $th) {
            var_dump($data);
            echo '<br />nem deu bom<br />';
            echo $th->getMessage();
        }
    }
    echo "<p>Serão importados $count_import comentários.</p>";
    echo 'Finalizada a validação da consistencia e contagem dos dados no csv</p></br>';
}

function validate_file_to_export()
{
    $products_collection = select_products();

    $reviews = [];
    foreach ($products_collection as $key => $value) {
        $reviewCollection = Mage::getModel('review/review')->getCollection()
            ->addFieldToFilter('entity_pk_value', array("in" => array($key)));

        foreach ($reviewCollection as $review) {
            $data_store = $review->getData();

            $data_store['detail'] = trim($data_store['detail']);
            if ($data_store['customer_id']) {
                $customer = Mage::getModel('customer/customer')->load($data_store['customer_id']);
                if ($customer && $customer->getId()) {
                    $data_store['customer_id'] = $customer->getEmail();
                }
            }
            $data_store['sku'] = $value->getSku();

            array_push($reviews, $data_store);
        }
    }

    echo '<p>Validação dos dados no arquivo de importação</p>';
    echo '<p>Os arquivos a baixo estão na loja antiga, mas não no csv de exportação</p>';
    $file = fopen(Mage::getBaseDir('var') . '/review.csv', 'r');
    $count_import = 0;
    $reviews_file = [];
    while (($line = fgets($file)) !== false) {
        try {
            if ($data_file = unserialize($line)) {
                $count_import += 1;

                array_push($reviews_file, $data_file);
            }
        } catch (Exception $th) {
            var_dump($data_file);
            echo '<br />nem deu bom<br />';
            echo $th->getMessage();
        }
    }

    $not_in = [];
    foreach ($reviews as $in_store) {
        $is_in = false;
        foreach ($reviews_file as $in_file) {
            if ($in_store == $in_file) {
                $is_in = true;
            };
        }

        if (!$is_in) {
            array_push($not_in, $in_store);
        }
    }

    var_dump($not_in);
    genarate_csv_by_array($not_in, 'reviews_diff');
    echo "<p>Forão encontrados " . count($not_in) . " comentários faltantes no csv.</p>";
    echo 'Finalizada a validação da consistencia e contagem dos dados no csv, se houverem divergencias, um csv reviews_diff com elas será gerado.</p></br>';
}