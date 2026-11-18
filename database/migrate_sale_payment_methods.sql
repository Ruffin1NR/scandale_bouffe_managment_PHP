-- ============================================================
-- Normalisation de sales.payment_method
-- ============================================================
-- Le mode global d'une vente est désormais déduit de ses lignes
-- (sale_items.pay_method). Valeurs possibles :
--   cash, prepaye, trosa, mobile, mixed
--
-- À exécuter une fois sur une base existante.
-- ============================================================

START TRANSACTION;

UPDATE sales s
SET s.payment_method = (
    SELECT
        CASE
            WHEN COUNT(DISTINCT si.pay_method) = 1 THEN MIN(si.pay_method)
            ELSE 'mixed'
        END
    FROM sale_items si
    WHERE si.sale_id = s.id
)
WHERE EXISTS (
    SELECT 1 FROM sale_items si WHERE si.sale_id = s.id
);

COMMIT;
