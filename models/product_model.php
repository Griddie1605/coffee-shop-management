<?php

class ProductModel extends Model
{
    public function getFeaturedProducts(int $limit = 6): array
    {
        $sql = "SELECT sp.id, sp.ten_san_pham, sp.gia_goc, sp.mo_ta, dm.ten_danh_muc, dm.loai_mon
                FROM san_pham sp
                INNER JOIN danh_muc dm ON sp.danh_muc_id = dm.id
                WHERE sp.con_ban = 1 AND dm.con_ban = 1
                ORDER BY sp.id ASC
                LIMIT :limit";

        $stmt = $this->db->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->fetchAll();
    }
}