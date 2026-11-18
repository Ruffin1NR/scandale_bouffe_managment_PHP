-- Journal des mouvements de stock pour suivre la continuité :
-- stock initial + entrées - sorties = stock final.
-- A executer apres database/migrate_track_stock.sql.

CREATE TABLE IF NOT EXISTS `stock_movements` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `product_id` BIGINT UNSIGNED NOT NULL,
    `movement_type` ENUM('initial', 'in', 'out') NOT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `reason` VARCHAR(50) NOT NULL DEFAULT 'manual',
    `note` VARCHAR(255) DEFAULT NULL,
    `sale_id` BIGINT UNSIGNED DEFAULT NULL,
    `user_id` BIGINT UNSIGNED DEFAULT NULL,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `stock_movements_product_id_created_at_index` (`product_id`, `created_at`),
    KEY `stock_movements_sale_id_index` (`sale_id`),
    KEY `stock_movements_user_id_index` (`user_id`),
    CONSTRAINT `stock_movements_product_id_foreign` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
    CONSTRAINT `stock_movements_sale_id_foreign` FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE SET NULL,
    CONSTRAINT `stock_movements_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Point de depart pour les produits déjà suivis : le stock actuel devient
-- le stock initial de la continuité. Les ventes passées ne sont pas reconstituées.
INSERT INTO `stock_movements` (`product_id`, `movement_type`, `quantity`, `reason`, `note`, `created_at`)
SELECT p.id, 'initial', p.quantity, 'initial', 'Stock initial à la mise en place', NOW()
FROM `products` p
JOIN `categories` c ON c.id = p.category_id
WHERE c.track_stock = 1
  AND NOT EXISTS (
      SELECT 1 FROM `stock_movements` sm WHERE sm.product_id = p.id
  );
