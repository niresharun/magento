<?php

namespace Ziffity\Dashboard\Block;

use Rootways\Megamenu\Block\Topmenu as MainMenu;
class Topmenu extends MainMenu
{
    protected function headerAreaHtml($main_cat)
    {
        $catHtml = '';
        if ($main_cat->getMegamenuTypeHeader() != '') {
            $catHtml .= '<div class="menuheader root-col-1 clearfix">';
            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_header'));
            $catHtml .= '</div>';
        }

        return $catHtml;
    }

    protected function footerAreaHtml($main_cat)
    {
        $catHtml = '';
        if ($main_cat->getMegamenuTypeFooter() != '') {
            $catHtml .= '<div class="menufooter root-col-1 clearfix">';
            $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_footer'));
            $catHtml .= '</div>';
        }

        return $catHtml;
    }

    protected function leftSideContentAreaHtml($main_cat, $left_content_area)
    {
        $catHtml = '<div class="'.$left_content_area.' clearfix rootmegamenu_block">';
        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_leftblock'));
        $catHtml .= '</div>';

        return $catHtml;
    }

    protected function rightSideContentAreaHtml($main_cat, $right_content_area)
    {
        $catHtml = '<div class="'.$right_content_area.' clearfix rootmegamenu_block">';
        $catHtml .= $this->getBlockContent($main_cat->getData('megamenu_type_rightblock'));
        $catHtml .= '</div>';

        return $catHtml;
    }

    /**
     * // 4th Level Category
     *
     * @param $navCnt0
     * @param $navCnt
     * @param $navCnt1
     * @param $collection_sub_sub
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function levelFourCategoriesWithLabelHtml($navCnt0, $navCnt, $navCnt1, $collection_sub_sub)
    {
        $catHtml = '';
        if (count($collection_sub_sub)) {
            $catHtml .= ' <ul class="rootmenu-submenu-sub-sub">';
            $navCnt2 = 0;
            foreach ($collection_sub_sub as $childCategory3) {
                $navCnt2++;
                $load_sub_3 = $this->categoryRepository->get($childCategory3->getId(), $this->_customhelper->getStoreId());
                $catHtml .= '<li class="nav-' . $navCnt0 . '-' . $navCnt . '-' . $navCnt1 . '-' . $navCnt2 . ' category-item"><a href="' . $load_sub_3->getURL() . '">' . $childCategory3->getName();
                if ($load_sub_3->getMegamenuTypeLabeltx() != '') {
                    $catHtml .= '<span class="top-sub-label"><em class="rootmenutag" style="background-color: #' . $load_sub_3->getMegamenuTypeLabelclr() . '">' . $load_sub_3->getMegamenuTypeLabeltx() . '</em></span>';
                }
                $catHtml .= '</a></li>';
            }
            $catHtml .= '</ul>';
        }

        return $catHtml;
    }
    protected function _getMenuItemClasses($item)
    {
        $classes = [];
        if ($this->_customhelper->getConfig('rootmegamenu_option/general/topmenuarrow') == 1) {
            if ($item->hasChildren()) {
                $classes[] = 'has-sub-cat';
            }
        }

        if ($this->_customhelper->getActionName() == 'catalog_category_view') {
            $cur_cat = $this->_customhelper->getcurrentCategory();
            $categoryPathIds = explode(',', $cur_cat->getPathInStore());
            if (in_array($item->getId(), $categoryPathIds) == '1') {
                $classes[] = 'active';
            }
        }
        return $classes;
    }

    public function getCustomLinks($category_id)
    {
        $base_url = rtrim($this->_storeManager->getStore()->getBaseUrl(), '/');
        $customMenus = $this->_customhelper->getConfig('rootmegamenu_option/general/custom_link', $this->_customhelper->getStoreId());
        $customLinkHtml = '';
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        if (!empty($customMenus) && $customMenus != '[]') {
            $customMenus = $this->_customhelper->getJsonDecode($customMenus);
            /*
            if ($this->_customhelper->getMagentoVersion() >= '2.2.0') {
                $customMenus = json_decode($customMenus, true);
            } else {
                $customMenus =  \Magento\Framework\Serialize\SerializerInterface::unserialize($customMenus);
            }*/
            if (is_array($customMenus)) {
                foreach ($customMenus as $customMenusRow) {
                    if ($customMenusRow['custommenulink'] != '') {
                        if (substr($customMenusRow['custommenulink'], 0, 1) != '/') {
                            $no_custom_link = $customMenusRow['custommenulink'];
                        } else {
                            $no_custom_link = $base_url.$customMenusRow['custommenulink'];
                        }
                    } else {
                        $no_custom_link = 'javascript:void(0);';
                    }
                    if (isset($customMenusRow['custom_menu_position'])) {
                        if ($customMenusRow['custom_menu_position'] == $category_id && $customMenusRow['custom_menu_position'] != '') {
                            $customLinkHtml .= $this->getCustomDropDownContent($no_custom_link, $customMenusRow);
                            /*
                            $customLinkHtml .= '<li class="custom-menus"><a href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>';
                            if ($customMenusRow['custom_menu_block'] != '') {
                                $customLinkHtml .= $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
                            }
                            $customLinkHtml .= '</li>';
                            */
                        }

                        if ($category_id == false &&
                            ($customMenusRow['custom_menu_position'] == 'default' ||
                                $customMenusRow['custom_menu_position'] == 'right' ||
                                $customMenusRow['custom_menu_position'] == 'left')
                        ) {
                            $customLinkHtml .= $this->getCustomDropDownContent($no_custom_link, $customMenusRow,$currentUrl);
                            /*
                            $customLinkHtml .= '<li class="custom-menus"><a href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>';
                            if ($customMenusRow['custom_menu_block'] != '') {
                                $customLinkHtml .= $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
                            }
                            $customLinkHtml .= '</li>';
                            */
                        }
                    }

                }
            }
        }
        return $customLinkHtml;
    }

    protected function getCustomDropDownContent($no_custom_link, $customMenusRow)
    {
        $dropDownHtml = '';
        $dropdownClass = 'custom-menus';
        $currentUrl = $this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]);
        if ($customMenusRow['custom_menu_block'] != '') {
            $content = $this->getLayout()->createBlock('Magento\Cms\Block\Block')->setBlockId($customMenusRow['custom_menu_block'])->toHtml();
            if (!empty($customMenusRow['custom_menu_layout'])) {
                if ($customMenusRow['custom_menu_layout'] == 4) {
                    $dropDownHtml = '<ul class="rootmenu-submenu">'.$content.'</ul>';
                } elseif ($customMenusRow['custom_menu_layout'] == 5) {
                    $dropDownHtml = '<ul class="rootmenu-submenu dropdown-leftside">'.$content.'</ul>';
                    $dropdownClass .= ' position-relative';
                } elseif ($customMenusRow['custom_menu_layout'] == 2) {
                    $dropDownHtml = '<div class="halfmenu clearfi">'.$content.'</div>';
                } elseif ($customMenusRow['custom_menu_layout'] == 3) {
                    $dropDownHtml = '<div class="halfmenu clearfi dropdown_left">'.$content.'</div>';
                    $dropdownClass .= ' dropdown_left';
                } else {
                    $dropDownHtml = '<div class="megamenu fullmenu clearfix categoriesmenu">'.$content.'</div>';
                }
            } else {
                $dropDownHtml = '<div class="megamenu fullmenu clearfix categoriesmenu">'.$content.'</div>';
            }
        }
        if ($customMenusRow['custom_menu_position'] == 'left' || $customMenusRow['custom_menu_position'] == 'right') {
            $dropdownClass .= ' rwcustomlink-'.$customMenusRow['custom_menu_position'];
        }
        if(str_contains($currentUrl,$customMenusRow['custommenulink'])){
            $result = '<li class="'.$dropdownClass.'"><a class="active" href="'.$no_custom_link.'">'.$customMenusRow['custommenuname'].'</a>'.$dropDownHtml.'</li>';
        }
        else {
            $result = '<li class="' . $dropdownClass . '"><a href="' . $no_custom_link . '">' . $customMenusRow['custommenuname'] . '</a>' . $dropDownHtml . '</li>';
        }
        return $result;
    }
    protected function masonryCategoryClass($cId)
    {
        $enableMasonry = $this->_customhelper->manageMasonry();
        $masonryClass = '';
        $colClass = 'root-col-';
        if ($enableMasonry == 1) {
            $masonryClass = ' grid';
            $colClass = 'grid-item-';
        } elseif ($enableMasonry == 2) {
            $masonryCategories = $this->_customhelper->masonryCategory();
            if (in_array($cId, $masonryCategories)) {
                $masonryClass = ' grid';
                $colClass = 'grid-item-';
            }
        } else {
            $masonryClass = '';
            $colClass = 'root-col-';
        }
        $msClasses = [$masonryClass, $colClass];
        return $msClasses;
    }
}
