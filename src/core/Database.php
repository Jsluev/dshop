<?php
/**
 * Database Manager
 *
 * @package DShop\Core
 */

namespace DShop\Core;

/**
 * Class Database
 *
 * Manages database operations and table creation
 */
class Database
{
    /**
     * WordPress database instance
     *
     * @var \wpdb
     */
    private $wpdb;

    /**
     * Table prefix
     *
     * @var string
     */
    private $prefix;

    /**
     * Constructor
     */
    public function __construct()
    {
        global $wpdb;
        $this->wpdb = $wpdb;
        $this->prefix = $wpdb->prefix . 'dshop_';
    }

    /**
     * Get table name
     *
     * @param string $table Table name without prefix
     * @return string Full table name
     */
    public function tableName(string $table): string
    {
        return $this->prefix . $table;
    }

    /**
     * Create all database tables
     *
     * @return void
     */
    public function createTables(): void
    {
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        $charset_collate = $this->wpdb->get_charset_collate();

        $this->createProductsTable($charset_collate);
        $this->createProductVariantsTable($charset_collate);
        $this->createCategoriesTable($charset_collate);
        $this->createAttributesTable($charset_collate);
        $this->createOrdersTable($charset_collate);
        $this->createOrderItemsTable($charset_collate);
        $this->createCustomersTable($charset_collate);
        $this->createCustomerGroupsTable($charset_collate);
        $this->createCouponsTable($charset_collate);
        $this->createWarehousesTable($charset_collate);
        $this->createStockTable($charset_collate);
        $this->createStockLogTable($charset_collate);
        $this->createReviewsTable($charset_collate);
        $this->createLogsTable($charset_collate);
    }

    /**
     * Create products table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createProductsTable(string $charset_collate): void
    {
        $table = $this->tableName('products');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            sku varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description longtext,
            short_description text,
            price decimal(10,2) NOT NULL DEFAULT 0,
            sale_price decimal(10,2) DEFAULT NULL,
            cost_price decimal(10,2) DEFAULT NULL,
            stock_quantity int(11) DEFAULT 0,
            stock_status varchar(20) DEFAULT 'instock',
            manage_stock tinyint(1) DEFAULT 0,
            weight decimal(10,3) DEFAULT NULL,
            length decimal(10,3) DEFAULT NULL,
            width decimal(10,3) DEFAULT NULL,
            height decimal(10,3) DEFAULT NULL,
            category_id bigint(20) DEFAULT NULL,
            type varchar(20) DEFAULT 'simple',
            status varchar(20) DEFAULT 'publish',
            featured tinyint(1) DEFAULT 0,
            is_virtual tinyint(1) DEFAULT 0,
            downloadable tinyint(1) DEFAULT 0,
            views int(11) DEFAULT 0,
            sales_count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY sku (sku),
            UNIQUE KEY slug (slug),
            KEY category_id (category_id),
            KEY status (status),
            KEY type (type),
            KEY price (price),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create product variants table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createProductVariantsTable(string $charset_collate): void
    {
        $table = $this->tableName('product_variants');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            sku varchar(100) NOT NULL,
            name varchar(255) NOT NULL,
            attributes longtext,
            price decimal(10,2) NOT NULL DEFAULT 0,
            sale_price decimal(10,2) DEFAULT NULL,
            stock_quantity int(11) DEFAULT 0,
            stock_status varchar(20) DEFAULT 'instock',
            weight decimal(10,3) DEFAULT NULL,
            length decimal(10,3) DEFAULT NULL,
            width decimal(10,3) DEFAULT NULL,
            height decimal(10,3) DEFAULT NULL,
            image_id bigint(20) DEFAULT NULL,
            status varchar(20) DEFAULT 'publish',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY sku (sku),
            KEY product_id (product_id),
            KEY status (status)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create categories table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createCategoriesTable(string $charset_collate): void
    {
        $table = $this->tableName('categories');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            parent_id bigint(20) DEFAULT 0,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            description text,
            image_id bigint(20) DEFAULT NULL,
            display_type varchar(20) DEFAULT 'default',
            position int(11) DEFAULT 0,
            count int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug),
            KEY parent_id (parent_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create attributes table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createAttributesTable(string $charset_collate): void
    {
        $table = $this->tableName('attributes');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            slug varchar(255) NOT NULL,
            type varchar(20) DEFAULT 'text',
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY slug (slug)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create orders table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createOrdersTable(string $charset_collate): void
    {
        $table = $this->tableName('orders');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_number varchar(50) NOT NULL,
            customer_id bigint(20) DEFAULT NULL,
            status varchar(20) DEFAULT 'new',
            currency varchar(3) DEFAULT 'RUB',
            subtotal decimal(10,2) NOT NULL DEFAULT 0,
            discount decimal(10,2) DEFAULT 0,
            shipping_cost decimal(10,2) DEFAULT 0,
            tax decimal(10,2) DEFAULT 0,
            total decimal(10,2) NOT NULL DEFAULT 0,
            billing_first_name varchar(100) DEFAULT '',
            billing_last_name varchar(100) DEFAULT '',
            billing_company varchar(100) DEFAULT '',
            billing_address_1 varchar(255) DEFAULT '',
            billing_address_2 varchar(255) DEFAULT '',
            billing_city varchar(100) DEFAULT '',
            billing_state varchar(100) DEFAULT '',
            billing_postcode varchar(20) DEFAULT '',
            billing_country varchar(2) DEFAULT 'RU',
            billing_phone varchar(20) DEFAULT '',
            billing_email varchar(100) DEFAULT '',
            shipping_first_name varchar(100) DEFAULT '',
            shipping_last_name varchar(100) DEFAULT '',
            shipping_company varchar(100) DEFAULT '',
            shipping_address_1 varchar(255) DEFAULT '',
            shipping_address_2 varchar(255) DEFAULT '',
            shipping_city varchar(100) DEFAULT '',
            shipping_state varchar(100) DEFAULT '',
            shipping_postcode varchar(20) DEFAULT '',
            shipping_country varchar(2) DEFAULT 'RU',
            payment_method varchar(50) DEFAULT '',
            payment_status varchar(20) DEFAULT 'pending',
            shipping_method varchar(50) DEFAULT '',
            customer_note text,
            admin_note text,
            ip_address varchar(45) DEFAULT '',
            user_agent text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY order_number (order_number),
            KEY customer_id (customer_id),
            KEY status (status),
            KEY payment_status (payment_status),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create order items table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createOrderItemsTable(string $charset_collate): void
    {
        $table = $this->tableName('order_items');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            order_id bigint(20) NOT NULL,
            product_id bigint(20) NOT NULL,
            variant_id bigint(20) DEFAULT NULL,
            name varchar(255) NOT NULL,
            sku varchar(100) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 1,
            price decimal(10,2) NOT NULL DEFAULT 0,
            total decimal(10,2) NOT NULL DEFAULT 0,
            tax decimal(10,2) DEFAULT 0,
            meta longtext,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY order_id (order_id),
            KEY product_id (product_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create customers table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createCustomersTable(string $charset_collate): void
    {
        $table = $this->tableName('customers');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            user_id bigint(20) DEFAULT NULL,
            email varchar(100) NOT NULL,
            phone varchar(20) DEFAULT '',
            first_name varchar(100) DEFAULT '',
            last_name varchar(100) DEFAULT '',
            company varchar(100) DEFAULT '',
            billing_address longtext,
            shipping_address longtext,
            group_id bigint(20) DEFAULT NULL,
            points_balance decimal(10,2) DEFAULT 0,
            total_spent decimal(10,2) DEFAULT 0,
            orders_count int(11) DEFAULT 0,
            last_order_at datetime DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY email (email),
            KEY user_id (user_id),
            KEY phone (phone)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create customer groups table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createCustomerGroupsTable(string $charset_collate): void
    {
        $table = $this->tableName('customer_groups');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            discount decimal(5,2) DEFAULT 0,
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create coupons table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createCouponsTable(string $charset_collate): void
    {
        $table = $this->tableName('coupons');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            code varchar(50) NOT NULL,
            type varchar(20) NOT NULL DEFAULT 'percent',
            amount decimal(10,2) NOT NULL DEFAULT 0,
            minimum_spend decimal(10,2) DEFAULT 0,
            maximum_spend decimal(10,2) DEFAULT 0,
            usage_limit int(11) DEFAULT NULL,
            usage_limit_per_user int(11) DEFAULT NULL,
            used_count int(11) DEFAULT 0,
            exclude_sale_items tinyint(1) DEFAULT 0,
            product_ids text,
            exclude_product_ids text,
            product_categories text,
            exclude_product_categories text,
            expires_at datetime DEFAULT NULL,
            status varchar(20) DEFAULT 'active',
            description text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            UNIQUE KEY code (code),
            KEY status (status),
            KEY expires_at (expires_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create warehouses table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createWarehousesTable(string $charset_collate): void
    {
        $table = $this->tableName('warehouses');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            name varchar(255) NOT NULL,
            address varchar(255) DEFAULT '',
            phone varchar(20) DEFAULT '',
            email varchar(100) DEFAULT '',
            is_default tinyint(1) DEFAULT 0,
            priority int(11) DEFAULT 0,
            status varchar(20) DEFAULT 'active',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY is_default (is_default),
            KEY status (status)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create stock table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createStockTable(string $charset_collate): void
    {
        $table = $this->tableName('stock');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            variant_id bigint(20) DEFAULT NULL,
            warehouse_id bigint(20) NOT NULL,
            quantity int(11) NOT NULL DEFAULT 0,
            reserved int(11) NOT NULL DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY variant_id (variant_id),
            KEY warehouse_id (warehouse_id)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create stock log table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createStockLogTable(string $charset_collate): void
    {
        $table = $this->tableName('stock_log');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            variant_id bigint(20) DEFAULT NULL,
            warehouse_id bigint(20) NOT NULL,
            quantity_change int(11) NOT NULL,
            reason varchar(255) DEFAULT '',
            order_id bigint(20) DEFAULT NULL,
            user_id bigint(20) DEFAULT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY warehouse_id (warehouse_id),
            KEY order_id (order_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create reviews table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createReviewsTable(string $charset_collate): void
    {
        $table = $this->tableName('reviews');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            product_id bigint(20) NOT NULL,
            customer_id bigint(20) DEFAULT NULL,
            order_id bigint(20) DEFAULT NULL,
            rating tinyint(1) NOT NULL DEFAULT 5,
            title varchar(255) DEFAULT '',
            content text,
            status varchar(20) DEFAULT 'pending',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY product_id (product_id),
            KEY customer_id (customer_id),
            KEY status (status)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Create logs table
     *
     * @param string $charset_collate Charset collation
     * @return void
     */
    private function createLogsTable(string $charset_collate): void
    {
        $table = $this->tableName('logs');

        $sql = "CREATE TABLE {$table} (
            id bigint(20) NOT NULL AUTO_INCREMENT,
            level varchar(20) NOT NULL DEFAULT 'info',
            message text NOT NULL,
            context longtext,
            user_id bigint(20) DEFAULT NULL,
            ip_address varchar(45) DEFAULT '',
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id),
            KEY level (level),
            KEY user_id (user_id),
            KEY created_at (created_at)
        ) {$charset_collate};";

        dbDelta($sql);
    }

    /**
     * Drop all database tables
     *
     * @return void
     */
    public function dropTables(): void
    {
        global $wpdb;

        $tables = [
            'products',
            'product_variants',
            'categories',
            'attributes',
            'orders',
            'order_items',
            'customers',
            'customer_groups',
            'coupons',
            'warehouses',
            'stock',
            'stock_log',
            'reviews',
            'logs',
        ];

        foreach ($tables as $table) {
            $table_name = $this->tableName($table);
            $wpdb->query("DROP TABLE IF EXISTS {$table_name}");
        }
    }

    /**
     * Get WordPress database instance
     *
     * @return \wpdb
     */
    public function getWpdb(): \wpdb
    {
        return $this->wpdb;
    }
}
