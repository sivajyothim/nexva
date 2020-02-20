<?php
/**
 * A view helper to get translation for inner page view categories 
 */
class Nexva_View_Helper_CategoryTranslation extends Zend_View_Helper_Abstract {

      public function CategoryTranslation($categoryName, $chapLangId) {
          $categoryModel = new Model_Category();
          $translation = $categoryModel->getCategoryTranslationByCategoryName($categoryName, $chapLangId);
          if($translation){
              return $translation->translation_title;
          }
          else{
              return $categoryName;
          }
      }
}
?>
