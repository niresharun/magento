<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Magento\GraphQl\CatalogPermissions\Product\Bundle;

use Magento\Bundle\Test\Fixture\Option as BundleOptionFixture;
use Magento\Bundle\Test\Fixture\Product as BundleProductFixture;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Catalog\Test\Fixture\Product as ProductFixture;
use Magento\Catalog\Test\Fixture\Category as CategoryFixture;
use Magento\TestFramework\Fixture\DataFixture;
use Magento\TestFramework\Fixture\DataFixtureStorage;
use Magento\TestFramework\Fixture\DataFixtureStorageManager;
use Magento\TestFramework\ObjectManager;
use Magento\TestFramework\TestCase\GraphQlAbstract;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\GraphQl\GetCustomerAuthenticationHeader;
use Magento\CatalogPermissions\Test\Fixture\Permission as PermissionFixture;
use Magento\CatalogPermissions\Model\Permission;

/**
 * Test presence/absence of bundle products via categories querying with various catalog permissions and customer groups
 */
class CategoriesQueryTest extends GraphQlAbstract
{
    /**
     * @var ObjectManager
     */
    private $objectManager;

    /**
     * @var DataFixtureStorage
     */
    private $fixtures;

    protected function setUp(): void
    {
        $this->objectManager = Bootstrap::getObjectManager();
        $this->fixtures = DataFixtureStorageManager::getStorage();
    }

    /**
     * Given Catalog Permissions are disabled
     * And 2 categories "Allowed Category" and "Denied Category" are created
     * And a permission is applied to "Allowed Category" granting all permissions to logged in customer group
     * And a permission is applied to "Denied Category" revoking all permissions to logged in customer group
     * And a bundle product is created in "Allowed Category"
     * And a bundle product is created in "Denied Category"
     * When a guest queries for categories and their products
     * Then all categories and products are returned in the response
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 0
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Allowed category'], 'allowed_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
            ],
            'simple_product_in_allowed_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Denied category'], 'denied_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
            ],
            'simple_product_in_denied_category'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'allowed_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_allowed_category$',
                ]
            ],
            'bundle_product_in_allowed_category_bundle_option'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'denied_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_denied_category$',
                ]
            ],
            'bundle_product_in_denied_category_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
                '_options' => ['$bundle_product_in_allowed_category_bundle_option$'],
            ],
            'bundle_product_in_allowed_category'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
                '_options' => ['$bundle_product_in_denied_category_bundle_option$'],
            ],
            'bundle_product_in_denied_category'
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$allowed_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_ALLOW,
                'grant_catalog_product_price' => Permission::PERMISSION_ALLOW,
                'grant_checkout_items' => Permission::PERMISSION_ALLOW,
            ]
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$denied_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_DENY,
                'grant_catalog_product_price' => Permission::PERMISSION_DENY,
                'grant_checkout_items' => Permission::PERMISSION_DENY,
            ]
        ),
    ]
    public function testCategoriesReturnedWhenPermissionsConfigurationDisabled()
    {
        $query = <<<QUERY
{
  categories(filters: {name: {match: "category"}}) {
    items {
      id
      name
      product_count
      products {
        items {
          sku
          ... on BundleProduct {
            dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                option_id
                title
                required
                type
                price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                    }
                    minimum_price {
                      regular_price {
                        value
                      }
                    }
                }
                position
                sku
                options {
                    id
                    quantity
                    position
                    is_default
                    price
                    price_type
                    can_change_quantity
                    label
                    product {
                        id
                        name
                        sku
                        type_id
                    }
                }
            }
         }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertCount(3, $response['categories']['items']);

        list($defaultCategory, $allowedCategory, $deniedCategory) = $response['categories']['items'];

        $this->assertEquals('Default Category', $defaultCategory['name']);

        $this->assertEquals('Allowed category', $allowedCategory['name']);
        $this->assertEqualsCanonicalizing(
            [
                'bundle-product-in-allowed-category',
                'simple-product-in-allowed-category',
            ],
            array_column($allowedCategory['products']['items'], 'sku')
        );

        $this->assertEquals('Denied category', $deniedCategory['name']);
        $this->assertEqualsCanonicalizing(
            [
                'bundle-product-in-denied-category',
                'simple-product-in-denied-category',
            ],
            array_column($deniedCategory['products']['items'], 'sku')
        );
    }

    /**
     * Given Catalog Permissions Are Enabled
     * And 2 categories "Allowed Category" and "Denied Category" are created
     * And a permission is applied to "Allowed Category" granting all permissions to logged in customer group
     * And a permission is applied to "Denied Category" revoking all permissions to logged in customer group
     * And a bundle product is created in "Allowed Category"
     * And a bundle product is created in "Denied Category"
     * When a guest queries for categories and their products
     * Then no categories or products are returned in the response
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 1
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Allowed category'], 'allowed_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
            ],
            'simple_product_in_allowed_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Denied category'], 'denied_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
            ],
            'simple_product_in_denied_category'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'allowed_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_allowed_category$',
                ]
            ],
            'bundle_product_in_allowed_category_bundle_option'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'denied_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_denied_category$',
                ]
            ],
            'bundle_product_in_denied_category_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
                '_options' => ['$bundle_product_in_allowed_category_bundle_option$'],
            ],
            'bundle_product_in_allowed_category'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
                '_options' => ['$bundle_product_in_denied_category_bundle_option$'],
            ],
            'bundle_product_in_denied_category'
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$allowed_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_ALLOW,
                'grant_catalog_product_price' => Permission::PERMISSION_ALLOW,
                'grant_checkout_items' => Permission::PERMISSION_ALLOW,
            ]
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$denied_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_DENY,
                'grant_catalog_product_price' => Permission::PERMISSION_DENY,
                'grant_checkout_items' => Permission::PERMISSION_DENY,
            ]
        ),
    ]
    public function testCategoriesReturnedForGuest()
    {
        $this->reindexCatalogPermissions();

        $query = <<<QUERY
{
  categories(filters: {name: {match: "category"}}) {
    items {
      id
      name
      product_count
      products {
        items {
          sku
          ... on BundleProduct {
            dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                option_id
                title
                required
                type
                price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                    }
                    minimum_price {
                      regular_price {
                        value
                      }
                    }
                }
                position
                sku
                options {
                    id
                    quantity
                    position
                    is_default
                    price
                    price_type
                    can_change_quantity
                    label
                    product {
                        id
                        name
                        sku
                        type_id
                    }
                }
            }
         }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertCount(0, $response['categories']['items']);
    }

    /**
     * Given Catalog Permissions are enabled
     * And 2 categories "Allowed Category" and "Denied Category" are created
     * And a permission is applied to "Allowed Category" granting all permissions to guest customer group
     * And a permission is applied to "Denied Category" revoking all permissions to guest customer group
     * And a bundle product is created in "Allowed Category"
     * And a bundle product is created in "Denied Category"
     * When a guest queries for categories and their products
     * Then only "Allowed Category" and its products are returned in the response
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 1
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Allowed category'], 'allowed_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
            ],
            'simple_product_in_allowed_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Denied category'], 'denied_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
            ],
            'simple_product_in_denied_category'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'allowed_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_allowed_category$',
                ]
            ],
            'bundle_product_in_allowed_category_bundle_option'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'denied_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_denied_category$',
                ]
            ],
            'bundle_product_in_denied_category_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
                '_options' => ['$bundle_product_in_allowed_category_bundle_option$'],
            ],
            'bundle_product_in_allowed_category'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
                '_options' => ['$bundle_product_in_denied_category_bundle_option$'],
            ],
            'bundle_product_in_denied_category'
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$allowed_category.id$',
                'customer_group_id' => 0, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_ALLOW,
                'grant_catalog_product_price' => Permission::PERMISSION_ALLOW,
                'grant_checkout_items' => Permission::PERMISSION_ALLOW,
            ]
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$denied_category.id$',
                'customer_group_id' => 0, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_DENY,
                'grant_catalog_product_price' => Permission::PERMISSION_DENY,
                'grant_checkout_items' => Permission::PERMISSION_DENY,
            ]
        ),
    ]
    public function testAllowedCategoriesReturnedForGuest()
    {
        $this->reindexCatalogPermissions();

        $query = <<<QUERY
{
  categories(filters: {name: {match: "category"}}) {
    items {
      id
      name
      product_count
      products {
        items {
          sku
          ... on BundleProduct {
            dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                option_id
                title
                required
                type
                price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                    }
                    minimum_price {
                      regular_price {
                        value
                      }
                    }
                }
                position
                sku
                options {
                    id
                    quantity
                    position
                    is_default
                    price
                    price_type
                    can_change_quantity
                    label
                    product {
                        id
                        name
                        sku
                        type_id
                    }
                }
            }
         }
        }
      }
    }
  }
}
QUERY;
        $response = $this->graphQlQuery($query);

        $this->assertCount(1, $response['categories']['items']);

        $allowedCategory = $response['categories']['items'][0];
        $this->assertEquals('Allowed category', $allowedCategory['name']);
        $this->assertEqualsCanonicalizing(
            [
                'bundle-product-in-allowed-category',
                'simple-product-in-allowed-category',
            ],
            array_column($allowedCategory['products']['items'], 'sku')
        );
    }

    /**
     * Given Catalog Permissions are enabled
     * And 2 categories "Allowed Category" and "Denied Category" are created
     * And a permission is applied to "Allowed Category" granting all permissions to logged in customer group
     * And a permission is applied to "Denied Category" revoking all permissions to logged in customer group
     * And a bundle product is created in "Allowed Category"
     * And a bundle product is created in "Denied Category"
     * When a logged in customer queries for categories and their products
     * Then only "Allowed Category" and its products are returned in the response
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Allowed category'], 'allowed_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
            ],
            'simple_product_in_allowed_category'
        ),
        DataFixture(CategoryFixture::class, ['name' => 'Denied category'], 'denied_category'),
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
            ],
            'simple_product_in_denied_category'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'allowed_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_allowed_category$',
                ]
            ],
            'bundle_product_in_allowed_category_bundle_option'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'denied_category_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                    '$simple_product_in_denied_category$',
                ]
            ],
            'bundle_product_in_denied_category_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-allowed-category',
                'category_ids' => ['$allowed_category.id$'],
                '_options' => ['$bundle_product_in_allowed_category_bundle_option$'],
            ],
            'bundle_product_in_allowed_category'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-denied-category',
                'category_ids' => ['$denied_category.id$'],
                '_options' => ['$bundle_product_in_denied_category_bundle_option$'],
            ],
            'bundle_product_in_denied_category'
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$allowed_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_ALLOW,
                'grant_catalog_product_price' => Permission::PERMISSION_ALLOW,
                'grant_checkout_items' => Permission::PERMISSION_ALLOW,
            ]
        ),
        DataFixture(
            PermissionFixture::class,
            [
                'category_id' => '$denied_category.id$',
                'customer_group_id' => 1, // General (i.e. logged in customer)
                'grant_catalog_category_view' => Permission::PERMISSION_DENY,
                'grant_catalog_product_price' => Permission::PERMISSION_DENY,
                'grant_checkout_items' => Permission::PERMISSION_DENY,
            ]
        ),
    ]
    public function testCategoriesReturnedForCustomer()
    {
        $this->reindexCatalogPermissions();

        $query = <<<QUERY
{
  categories(filters: {name: {match: "category"}}) {
    items {
      id
      name
      product_count
      products {
        items {
          sku
          ... on BundleProduct {
            dynamic_sku
            dynamic_price
            dynamic_weight
            price_view
            ship_bundle_items
            items {
                option_id
                title
                required
                type
                price_range {
                    maximum_price {
                      regular_price {
                        value
                      }
                    }
                    minimum_price {
                      regular_price {
                        value
                      }
                    }
                }
                position
                sku
                options {
                    id
                    quantity
                    position
                    is_default
                    price
                    price_type
                    can_change_quantity
                    label
                    product {
                        id
                        name
                        sku
                        type_id
                    }
                }
            }
         }
        }
      }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->objectManager->get(GetCustomerAuthenticationHeader::class)->execute($currentEmail, $currentPassword)
        );

        $this->assertCount(1, $response['categories']['items']);

        $allowedCategory = $response['categories']['items'][0];
        $this->assertEquals('Allowed category', $allowedCategory['name']);
        $this->assertEqualsCanonicalizing(
            [
                'bundle-product-in-allowed-category',
                'simple-product-in-allowed-category',
            ],
            array_column($allowedCategory['products']['items'], 'sku')
        );
        $bundleProduct = $allowedCategory['products']['items'][0];
        $this->assertEquals('bundle-product-in-allowed-category', $bundleProduct['sku']);
        $bundleProductPriceRange = $bundleProduct['items'][0]['price_range'];
        $this->assertEquals(20, $bundleProductPriceRange['maximum_price']['regular_price']['value']);
        $this->assertEquals(10, $bundleProductPriceRange['minimum_price']['regular_price']['value']);
    }

    /**
     * Given Catalog permissions are enabled
     * And "Allow Browsing Category" is set to "Yes, for Specified Customer Groups"
     * And "Customer Groups" is set to "General" (i.e. a logged in user)
     * And a bundle product is assigned to a category that does not have any permissions applied
     * When a logged in customer queries for a specific category and its products by its id
     * Then the category and its containing product are returned
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture catalog/magento_catalogpermissions/grant_catalog_category_view 2
     * @magentoConfigFixture catalog/magento_catalogpermissions/grant_catalog_category_view_groups 1
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 1',
                'parent_id' => 2,
                'is_anchor' => 1,
            ],
            'c1'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 2',
                'parent_id' => '$c1.id$',
            ],
            'c2'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 3',
                'parent_id' => '$c1.id$',
            ],
            'c3'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'category_with_no_permissions_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                ]
            ],
            'bundle_product_in_category_without_permissions_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-category-without-permissions-applied',
                'category_ids' => ['$c2.id$'],
                '_options' => ['$bundle_product_in_category_without_permissions_bundle_option$'],
            ],
            'bundle_product_in_category_without_permissions_applied'
        ),
    ]
    public function testCategoriesReturnedForCustomerGroupsAllowedByConfiguration()
    {
        $this->reindexCatalogPermissions();

        /** @var CategoryInterface $category1 */
        $category1 = $this->fixtures->get('c1');
        $category1Id = $category1->getId();
        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "$category1Id"}}) {
    total_count
    items {
        id
        name
        children_count
        products {
            items {
                sku
            }
        }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->objectManager->get(GetCustomerAuthenticationHeader::class)->execute($currentEmail, $currentPassword)
        );

        $this->assertEquals(1, $response['categories']['total_count']);
        $this->assertCount(1, $response['categories']['items']);
        $this->assertEquals(2, $response['categories']['items'][0]['children_count']);
        $this->assertCount(1, $response['categories']['items'][0]['products']['items']);
        $this->assertEqualsCanonicalizing(
            [
                'bundle-product-in-category-without-permissions-applied',
            ],
            array_column($response['categories']['items'][0]['products']['items'], 'sku')
        );
    }

    /**
     * Given Catalog permissions are enabled
     * And "Allow Browsing Category" is set to "Yes, for Specified Customer Groups"
     * And "Customer Groups" is set to "Wholesale"
     * And a bundle product is assigned to a category that does not have any permissions applied
     * When a logged in customer queries for a specific category and its products by its id
     * Then no category or products are returned in the response
     *
     * @magentoConfigFixture catalog/magento_catalogpermissions/enabled 1
     * @magentoConfigFixture catalog/magento_catalogpermissions/grant_catalog_category_view 2
     * @magentoConfigFixture catalog/magento_catalogpermissions/grant_catalog_category_view_groups 2
     * @magentoApiDataFixture Magento/Customer/_files/customer.php
     */
    #[
        DataFixture(
            ProductFixture::class,
            [
                'sku' => 'simple',
                'category_ids' => [2], // Default Category
            ],
            'simple_product_in_default_category'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 1',
                'parent_id' => 2,
                'is_anchor' => 1,
            ],
            'c1'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 2',
                'parent_id' => '$c1.id$',
            ],
            'c2'
        ),
        DataFixture(
            CategoryFixture::class,
            [
                'name' => 'Category 3',
                'parent_id' => '$c1.id$',
            ],
            'c3'
        ),
        DataFixture(
            BundleOptionFixture::class,
            [
                'title' => 'category_with_no_permissions_bundle_option',
                'type' => 'checkbox',
                'product_links' => [
                    '$simple_product_in_default_category$',
                ]
            ],
            'bundle_product_in_category_without_permissions_bundle_option'
        ),
        DataFixture(
            BundleProductFixture::class,
            [
                'sku' => 'bundle-product-in-category-without-permissions-applied',
                'category_ids' => ['$c2.id$'],
                '_options' => ['$bundle_product_in_category_without_permissions_bundle_option$'],
            ],
            'bundle_product_in_category_without_permissions_applied'
        ),
    ]
    public function testCategoriesReturnedForCustomerGroupsDeniedByConfiguration()
    {
        $this->reindexCatalogPermissions();

        /** @var CategoryInterface $category1 */
        $category1 = $this->fixtures->get('c1');
        $category1Id = $category1->getId();

        $query = <<<QUERY
{
  categories(filters: {ids: {eq: "$category1Id"}}) {
    total_count
    items {
        children_count
        products {
            items {
                id
            }
        }
    }
  }
}
QUERY;
        $currentEmail = 'customer@example.com';
        $currentPassword = 'password';
        $response = $this->graphQlQuery(
            $query,
            [],
            '',
            $this->objectManager->get(GetCustomerAuthenticationHeader::class)->execute($currentEmail, $currentPassword)
        );

        $this->assertEmpty($response['categories']['items']);
    }

    /**
     * Reindex catalog permissions
     */
    private function reindexCatalogPermissions()
    {
        $appDir = dirname(Bootstrap::getInstance()->getAppTempDir());

        // phpcs:ignore Magento2.Security.InsecureFunction
        exec("php -f {$appDir}/bin/magento indexer:reindex catalogpermissions_category");
    }
}
