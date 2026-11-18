-- ============================================================
-- Remise à zéro de l'activité - Scandale Bouffe
-- ============================================================
-- ATTENTION : opération destructive et irréversible.
-- À exécuter uniquement après une sauvegarde complète de la base.
--
-- Ce script :
--   1. supprime toutes les ventes et leurs lignes ;
--   2. purge l'historique des mouvements de stock ;
--   3. purge les rapports journaliers et les saisies de caisse ;
--   4. remet les produits suivis à leur stock initial d'origine ;
--   5. remet à zéro le stock des produits non suivis ;
--   6. recrée un mouvement "initial" par produit suivi.
--
-- Le stock initial est repris des mouvements "initial" existants.
-- ============================================================

USE scandale_bouffe;

START TRANSACTION;

-- ------------------------------------------------------------
-- 1. Mémoriser le stock initial avant la purge des mouvements
-- ------------------------------------------------------------
DROP TEMPORARY TABLE IF EXISTS tmp_stock_initial;
CREATE TEMPORARY TABLE tmp_stock_initial (
    product_id BIGINT UNSIGNED NOT NULL PRIMARY KEY,
    quantity INT NOT NULL DEFAULT 0
);

INSERT INTO tmp_stock_initial (product_id, quantity)
SELECT product_id, COALESCE(SUM(quantity), 0)
FROM stock_movements
WHERE movement_type = 'initial'
GROUP BY product_id;

-- ------------------------------------------------------------
-- 2. Supprimer les ventes (sale_items est supprimé en cascade)
-- ------------------------------------------------------------
DELETE FROM sales;

-- ------------------------------------------------------------
-- 3. Purger l'historique des mouvements de stock
-- ------------------------------------------------------------
DELETE FROM stock_movements;

-- ------------------------------------------------------------
-- 4. Purger les rapports journaliers et les saisies de caisse
-- ------------------------------------------------------------
DELETE FROM daily_reports;
DELETE FROM daily_deposits;
DELETE FROM daily_relais;
DELETE FROM daily_paiements;

-- ------------------------------------------------------------
-- 5. Remettre les produits suivis à leur stock initial
-- ------------------------------------------------------------
UPDATE products p
JOIN categories c ON c.id = p.category_id
LEFT JOIN tmp_stock_initial t ON t.product_id = p.id
SET p.quantity = COALESCE(t.quantity, 0),
    p.updated_at = NOW()
WHERE c.track_stock = 1;

-- Les produits non suivis ne portent pas de stock
UPDATE products p
LEFT JOIN categories c ON c.id = p.category_id
SET p.quantity = 0,
    p.updated_at = NOW()
WHERE COALESCE(c.track_stock, 0) = 0
  AND p.quantity <> 0;

COMMIT;

-- ------------------------------------------------------------
-- 6. Remettre le compteur des mouvements à zéro
--    puis recréer le mouvement initial de chaque produit suivi
-- ------------------------------------------------------------
ALTER TABLE stock_movements AUTO_INCREMENT = 1;

START TRANSACTION;

INSERT INTO stock_movements
    (product_id, movement_type, quantity, reason, note, sale_id, user_id, created_at)
SELECT
    p.id,
    'initial',
    p.quantity,
    'initial',
    'Réinitialisation de l''activité',
    NULL,
    NULL,
    NOW()
FROM products p
JOIN categories c ON c.id = p.category_id
WHERE c.track_stock = 1;

COMMIT;

-- ------------------------------------------------------------
-- 7. Remettre les compteurs AUTO_INCREMENT des tables vidées à 1
-- ------------------------------------------------------------
ALTER TABLE sales AUTO_INCREMENT = 1;
ALTER TABLE sale_items AUTO_INCREMENT = 1;
ALTER TABLE daily_reports AUTO_INCREMENT = 1;
ALTER TABLE daily_deposits AUTO_INCREMENT = 1;
ALTER TABLE daily_relais AUTO_INCREMENT = 1;
ALTER TABLE daily_paiements AUTO_INCREMENT = 1;

DROP TEMPORARY TABLE IF EXISTS tmp_stock_initial;
