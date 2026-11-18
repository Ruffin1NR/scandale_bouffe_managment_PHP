-- Suivi de stock limite aux categories marquees track_stock = 1
-- A executer une seule fois sur une base existante.

ALTER TABLE `categories`
    ADD COLUMN IF NOT EXISTS `track_stock` TINYINT(1) NOT NULL DEFAULT 0 AFTER `description`;

UPDATE `categories`
SET `track_stock` = CASE WHEN `name` = 'Revente' THEN 1 ELSE 0 END;

-- Les produits des categories non suivies ne doivent plus conserver de stock.
UPDATE `products` p
LEFT JOIN `categories` c ON c.id = p.category_id
SET p.quantity = 0,
    p.min_stock = 0
WHERE COALESCE(c.track_stock, 0) = 0;
