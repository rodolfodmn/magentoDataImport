<a href="./">voltar</a>
<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ini_set('xdebug.var_display_max_depth', -1);
ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);

include '../../app/Mage.php';
Mage::app();
validateOption($_GET['option']);

exit;
switch ($_GET['opcao']) {
    case '1':
        echo 'Iniciando Exportação de comentários (Opnioes Verificadas)</br>';
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
            Mage::log((string) $reviewCollection->getSelect(), null, 'log.log', true);
            // Mage::log($key,null,'log.log',true);
            foreach ($reviewCollection as $review) {
                $data = $review->getData();

                $data['detail'] = trim($data['detail']);
                if ($data['customer_id']) {
                    $customer = Mage::getModel('customer/customer')->load($data['customer_id']);
                    if ($customer && $customer->getId()) {
                        $data['customer_id'] = $customer->getEmail();
                    }
                }
                $data['sku'] = $value->getSku();
                var_dump($data);
                fwrite($file, serialize($data) . "\n");
            }
        }
        fclose($file);
        echo 'Finalizada a Exportação de comentários </br>';
        break;
    case '2':
        echo 'Iniciando Importação de comentários (Opnioes Verificadas)</br>';
        $file = fopen(Mage::getBaseUrl() . '/review.csv', 'r');
        while (($line = fgets($file)) !== false) {
            try {
                if ($data = unserialize($line)) {
                    // Mage::log($data,null,'log.log',true);
                    if ($data['customer_id']) {
                        $customer = Mage::getModel('customer/customer')
                            ->setWebsiteId(Mage::app()->getStore()->getWebsiteId())
                            ->loadByEmail($data['customer_id']);
                        if ($customer && $customer->getId()) {
                            $data['customer_id'] = $customer->getId();
                        } else {
                            $data['customer_id'] = null;
                        }

                    }
                    unset($data['detail_id']);
                    unset($data['review_id']);
                    var_dump($data);
                    exit;
                    $review = Mage::getModel('review/review')->setData($data);
                    $review->setEntityId($review->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                        ->setEntityPkValue($data['entity_pk_value'])
                        ->setStatusId(Mage_Review_Model_Review::STATUS_PENDING)
                        ->setStoreId(Mage::app()->getStore()->getId())
                        ->setStores(array(Mage::app()->getStore()->getId()));
                    $review->save();
                    // var_dump($review->getProductCollection());
                    Mage::getModel('rating/rating')
                        ->setRatingId(1)
                        ->setReviewId($review->getId());

                    // ->addOptionVote($data['rate'], $prodId);

                    $review->save();
                    $review->aggregate();
                    echo '<br />num é que deu bom :)<br />';
                }
                var_dump($data);
                echo '<br />Faaaalllsee :(<br />';

            } catch (Exception $th) {
                var_dump($data);
                echo '<br />nem deu bom<br />';
                echo $th->getMessage();
            }
        }
        echo 'Finalizada a Importação de comentários </br>';
        break;
    case '3':
        echo 'Iniciando validação da consistencia dos dados</br>';
        $file = fopen(Mage::getBaseUrl() . '/review.csv', 'r');
        var_dump($file);
        while (($line = fgets($file)) !== false) {
            try {
                $data = unserialize($line);
                var_dump($data);
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        echo 'Finalizada a validação da consistencia dos dados </br>';
        break;
    case '4':
        echo 'Iniciando identificação dos dados incorretos</br>';
        Mage::register('isSecureArea', true);
        $file = fopen(Mage::getBaseUrl() . '/review.csv', 'r');
        var_dump($file);
        var_dump(count($reviewCollection = Mage::getModel('review/review')->getCollection()));
        $count_import = 0;
        while (($line = fgets($file)) !== false) {
            try {
                $data = unserialize($line);
                $product = Mage::getModel('catalog/product')->loadByAttribute('sku', $data['sku']);
                if ($data['detail'] != null && $product) {
                    $reviewCollection = Mage::getModel('review/review')->getCollection()
                        ->addFieldToFilter('detail', array("like" => $data['detail']))
                        ->addFieldToFilter('title', array("like" => $data['title']));
                    foreach ($reviewCollection as $key => $value) {

                        var_dump($value);
                        $value->setEntityId($value->getEntityIdByCode(Mage_Review_Model_Review::ENTITY_PRODUCT_CODE))
                            ->setEntityPkValue($product->getId());

                        $value->save();
                        break;
                    }
                    $count_import += count($reviewCollection);
                }
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        var_dump($count_import);
        echo 'Finalizada a validação da consistencia dos dados </br>';
        break;
    case '5':
        echo 'contegem dos intens para serem importados</br>';
        $file = fopen(Mage::getBaseUrl() . '/review.csv', 'r');
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
        var_dump($count_import);
        echo 'Finalizada a validação da consistencia dos dados </br>';
        break;
    case '6':
        echo 'teste';
        $proxy = new SoapClient('http://localhost/jfsun/api/v2_soap/?wsdl'); // TODO : change url
        $sessionId = $proxy->login('admin', 'admin12'); // TODO : change login and pwd if necessary

        $result = $proxy->catalogProductInfo($sessionId, '601');
        var_dump($result->getReviews());
        break;
    default:
        echo 'Opção não encontrada </br>';
        break;
}

function validateOption($option = null){

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
            break;
        case '4':
            break;
        case '5':
            break;
        case '6':
            break;
        default:
            break;
    }
}

function see_origin_data()
{
    echo '<p>Esses são os dados que serão importados:</p></hr>';
    $collection = mage::getModel('catalog/product')->getCollection();
    $select = $collection->getSelect();
    $select->reset(Zend_Db_Select::COLUMNS);
    $select->columns('entity_id');
    $select->columns('sku');
    $productResource = Mage::getModel('catalog/product')->getResource();

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