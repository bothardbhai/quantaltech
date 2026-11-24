<?php
/**
 * Admin — Service Subcategories (full CRUD)
 *
 * Grouping master for Services (Build AI, AI Consulting, AI Applications...)
 * used to organize services in the admin and the Services mega menu.
 * See db/migrations/2026-11-02-001-create-service-subcategories.sql.
 * Grouping only — never part of a service URL (/services/{slug}).
 *
 * The CRUD itself is shared with admin/hire-subcategories.php:
 * see admin/_subcategory-master.php.
 */

require __DIR__ . '/bootstrap.php';

$subcat_cfg = [
    'table'       => 'service_subcategories',
    'item_table'  => 'services',
    'active'      => 'service-subcategories',
    'title'       => 'Service Subcategories',
    'singular'    => 'Service Subcategory',
    'item_one'    => 'service',
    'item_many'   => 'services',
    'items_url'   => 'services.php',
    'items_label' => 'All Services',
    'example'     => 'e.g. Build AI',
    'nav_label'   => 'Services',
];

require __DIR__ . '/_subcategory-master.php';
