<?php
/**
 * Admin — Hire Subcategories (full CRUD)
 *
 * Grouping master for Hire Master pages (e.g. AI & ML Engineers, Specialized
 * AI Engineers) used to organize hire pages in the admin and the
 * "Hire with Us" mega menu.
 * See db/migrations/2026-11-02-003-create-hire-subcategories.sql.
 * Grouping only — never part of a hire page URL (/hire/{slug}).
 *
 * The CRUD itself is shared with admin/service-subcategories.php:
 * see admin/_subcategory-master.php.
 */

require __DIR__ . '/bootstrap.php';

$subcat_cfg = [
    'table'       => 'hire_subcategories',
    'item_table'  => 'hire_pages',
    'active'      => 'hire-subcategories',
    'title'       => 'Hire Subcategories',
    'singular'    => 'Hire Subcategory',
    'item_one'    => 'hire page',
    'item_many'   => 'hire pages',
    'items_url'   => 'hire.php',
    'items_label' => 'All Hire Pages',
    'example'     => 'e.g. AI & ML Engineers',
    'nav_label'   => 'Hire with Us',
];

require __DIR__ . '/_subcategory-master.php';
