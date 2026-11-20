-- ============================================================
-- Contributions des ventes aux rapports journaliers
-- ============================================================
-- Chaque vente enregistrée via le POS ajoute ses montants aux
-- colonnes prepaye / trosa / paiement / nofy de daily_reports.
-- Cette table permet de savoir exactement ce qu'une vente a
-- ajouté au rapport, afin de le retirer proprement si la vente
-- est supprimée (sans recalculer le rapport ni toucher aux
-- valeurs saisies manuellement).
--
-- À exécuter une fois sur une base existante.
-- ============================================================

CREATE TABLE IF NOT EXISTS `sale_report_contributions` (
    `sale_id` bigint(20) unsigned NOT NULL,
    `user_id` int(11) NOT NULL,
    `report_date` date NOT NULL,
    `prepaye` decimal(12,2) NOT NULL DEFAULT 0.00,
    `trosa` decimal(12,2) NOT NULL DEFAULT 0.00,
    `paiement` decimal(12,2) NOT NULL DEFAULT 0.00,
    `nofy` decimal(12,2) NOT NULL DEFAULT 0.00,
    `created_at` datetime NOT NULL DEFAULT current_timestamp(),
    PRIMARY KEY (`sale_id`),
    KEY `sale_report_contributions_user_date_index` (`user_id`, `report_date`),
    CONSTRAINT `sale_report_contributions_sale_id_foreign`
        FOREIGN KEY (`sale_id`) REFERENCES `sales` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
