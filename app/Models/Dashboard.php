<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

use Illuminate\Support\Facades\DB;

use Auth;
use Illuminate\Support\Facades\Gate;

class Dashboard extends Model
{


    public static function getCount($year = null,$location_id = null){

        $where = '';
        if($location_id){
            $where =  " AND location_id =". $location_id;
        }
        $sql = "

            SELECT
            COUNT(*) as count
            FROM purchases
            WHERE EXTRACT(YEAR from created_at) = '$year'
            $where

            UNION ALL

            SELECT
            COUNT(*) as count
            FROM purchase_requisitions
            WHERE EXTRACT(YEAR from created_at) = '$year'
            $where

            UNION ALL

            SELECT 
            COUNT(*) as count
            FROM po
            LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
            WHERE EXTRACT(YEAR from po.created_at) = $year
            $where
           
            UNION ALL

            SELECT
            COUNT(*) as count
            FROM lpb
            LEFT JOIN po ON po.id = lpb.po_id
            LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
            WHERE lpb.status != 0
            AND EXTRACT(YEAR from lpb.created_at) = $year
            $where

            UNION ALL

            SELECT
            COUNT(*) as count
            FROM spb
            WHERE spb.status != 0
            AND EXTRACT(YEAR from spb.created_at) = $year
            $where

            UNION ALL

            SELECT
            COUNT(*) as count
            FROM bpb
            WHERE bpb.status != 0
            AND po_id IS NULL
            AND EXTRACT(YEAR from bpb.created_at) = $year
            $where

            UNION ALL

            SELECT
            COUNT(*) as count
            FROM bpb
            WHERE bpb.status != 0
            AND spb_id IS NULL
            AND EXTRACT(YEAR from bpb.created_at) = $year
            $where

        ";
        return DB::select( DB::raw($sql));

    }



    public static function getStatistic($year = null,$location_id = null){

        $where = '';
        if($location_id){
            $where =  " AND location_id =". $location_id;
        }
        
        $sql = "

            SELECT 
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Jan' THEN 1 ELSE 0 END),0) AS jan,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Feb' THEN 1 ELSE 0 END),0) AS feb,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Mar' THEN 1 ELSE 0 END),0) AS mar,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Apr' THEN 1 ELSE 0 END),0) AS apr,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'May' THEN 1 ELSE 0 END),0) AS mei,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Jun' THEN 1 ELSE 0 END),0) AS jun,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Jul' THEN 1 ELSE 0 END),0) AS jul,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Aug' THEN 1 ELSE 0 END),0) AS aug,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Sep' THEN 1 ELSE 0 END),0) AS sep,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Oct' THEN 1 ELSE 0 END),0) AS oct,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Nov' THEN 1 ELSE 0 END),0) AS nov,
            COALESCE(SUM(CASE WHEN to_char(purchases.created_at,'Mon') = 'Dec' THEN 1 ELSE 0 END),0) AS dec
            FROM purchases
            WHERE EXTRACT(YEAR from created_at) = $year
            $where

            UNION ALL

            SELECT 
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Jan' THEN 1 ELSE 0 END),0) AS jan,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Feb' THEN 1 ELSE 0 END),0) AS feb,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Mar' THEN 1 ELSE 0 END),0) AS mar,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Apr' THEN 1 ELSE 0 END),0) AS apr,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'May' THEN 1 ELSE 0 END),0) AS mei,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Jun' THEN 1 ELSE 0 END),0) AS jun,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Jul' THEN 1 ELSE 0 END),0) AS jul,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Aug' THEN 1 ELSE 0 END),0) AS aug,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Sep' THEN 1 ELSE 0 END),0) AS sep,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Oct' THEN 1 ELSE 0 END),0) AS oct,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Nov' THEN 1 ELSE 0 END),0) AS nov,
            COALESCE(SUM(CASE WHEN to_char(purchase_requisitions.created_at,'Mon') = 'Dec' THEN 1 ELSE 0 END),0) AS dec
            FROM purchase_requisitions
            WHERE EXTRACT(YEAR from created_at) = $year
            $where

            UNION ALL

            SELECT 
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Jan' THEN 1 ELSE 0 END),0) AS jan,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Feb' THEN 1 ELSE 0 END),0) AS feb,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Mar' THEN 1 ELSE 0 END),0) AS mar,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Apr' THEN 1 ELSE 0 END),0) AS apr,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'May' THEN 1 ELSE 0 END),0) AS mei,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Jun' THEN 1 ELSE 0 END),0) AS jun,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Jul' THEN 1 ELSE 0 END),0) AS jul,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Aug' THEN 1 ELSE 0 END),0) AS aug,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Sep' THEN 1 ELSE 0 END),0) AS sep,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Oct' THEN 1 ELSE 0 END),0) AS oct,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Nov' THEN 1 ELSE 0 END),0) AS nov,
            COALESCE(SUM(CASE WHEN to_char(po.created_at,'Mon') = 'Dec' THEN 1 ELSE 0 END),0) AS dec
            FROM po
            LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
            WHERE EXTRACT(YEAR from purchase_requisitions.created_at) = $year
            $where

        ";
        return DB::select( DB::raw($sql));

    }

    public static function getStatisticLogistik6Mount() {
        $where = '';
        if(GATE::allows('admin_dpm')){
            $where =  " AND purchases.created_by =". Auth::user()->id;
        }
        $sql = "
            SELECT
                'DPM' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM purchases
            WHERE purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                AND purchases.status IS NOT NULL
                AND purchases.type = 'po'
                $where

            UNION ALL

            SELECT
                'PR' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM purchase_requisitions
            LEFT JOIN purchases ON purchases.id = purchase_requisitions.purchase_id
            WHERE purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                AND purchases.type = 'po'
            $where

            UNION ALL

            SELECT
                'PO Jakarta' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM po
            LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
            LEFT JOIN purchases ON purchases.id = purchase_requisitions.purchase_id
            WHERE purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                AND po.status IS NOT NULL
                AND po.type = 'lpb'
                AND purchases.type = 'po'
                $where

            UNION ALL

            SELECT
                'PO Lokal' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM purchases.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM po
            LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
            LEFT JOIN purchases ON purchases.id = purchase_requisitions.purchase_id
            WHERE purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                AND po.status IS NOT NULL
                AND po.type = 'non_lpb'
                AND purchases.type = 'po'
                $where

            UNION ALL

            SELECT
                'Done PO Jakarta' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM (
                SELECT DISTINCT
                    po.id,
                    purchases.created_at,
                    bpb.status
                FROM purchases
                LEFT JOIN purchase_requisitions ON purchases.id = purchase_requisitions.purchase_id
                LEFT JOIN po ON po.purchase_id = purchase_requisitions.id
                LEFT JOIN po_items ON po.id = po_items.po_id
                LEFT JOIN lpb_items ON lpb_items.pr_item_id = po_items.pr_item_id
                LEFT JOIN lpb ON lpb.id = lpb_items.lpb_id
                LEFT JOIN bpb_items ON po_items.pr_item_id = bpb_items.pr_item_id
                LEFT JOIN bpb ON bpb.id = bpb_items.bpb_id
                WHERE
                    purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                    AND po.status IS NOT NULL
                    AND po.type = 'lpb'
                    AND purchases.type = 'po'
                    $where
                    AND (
                        (lpb.status != 3 AND bpb.id IS NOT NULL)
                        OR (lpb.status = 3)
                    )

            ) AS grouped_po


            UNION ALL

            SELECT
                'Done PO Lokal' AS category,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE) THEN 1 ELSE 0 END), 0) AS current_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '1 month') THEN 1 ELSE 0 END), 0) AS last_month,
                COALESCE(SUM(CASE WHEN EXTRACT(MONTH FROM grouped_po.created_at) = EXTRACT(MONTH FROM CURRENT_DATE - INTERVAL '2 months') THEN 1 ELSE 0 END), 0) AS two_months_ago
            FROM (
                SELECT DISTINCT
                    po.id,
                    purchases.created_at,
                    bpb.status
                FROM po
                LEFT JOIN bpb ON bpb.po_id = po.id
                LEFT JOIN purchase_requisitions ON purchase_requisitions.id = po.purchase_id
                LEFT JOIN purchases ON purchases.id = purchase_requisitions.purchase_id
                WHERE
                    purchases.created_at >= DATE_TRUNC('month', CURRENT_DATE - INTERVAL '2 months')
                    AND po.status IS NOT NULL
                    AND bpb.spb_id IS NULL
                    AND bpb.id IS NOT NULL
                    AND po.type = 'non_lpb'
                    AND po.status = 5
                    AND purchases.type = 'po'
                    $where
            ) AS grouped_po
        ";

        return DB::select(DB::raw($sql));
    }

    public static function getCountPRByProject()
    {
        $sql = "SELECT t1.name, t1.id, t2.num
        FROM projects t1 
        LEFT JOIN (
                SELECT purchase_requisitions.project_id, count(purchase_requisitions.id) As num
                FROM purchase_requisitions 
                WHERE purchase_requisitions.status IN (0,2)
                GROUP BY project_id
            ) t2 ON t1.id = t2.project_id 
        ORDER BY num DESC";

        return DB::select( DB::raw($sql));
    }


    public static function getCountPOByLocation()
    {
        $sql = 'SELECT t1.name, t1.company_id, t3.name AS company, t2.num
        FROM locations t1
        LEFT OUTER JOIN 
            (SELECT purchase_requisitions.location_id, COUNT(po.id) AS num
            FROM po
            LEFT JOIN purchase_requisitions ON po.purchase_id = purchase_requisitions.id
            WHERE po.status IN (2,4,5)
            GROUP BY purchase_requisitions.location_id
            ) t2
        ON t1.id = t2.location_id
        LEFT JOIN companies t3 ON t3.id = t1.company_id 
        ORDER BY t1.name ASC';

        return DB::select( DB::raw($sql));
    }

    public static function getCountQtyItemLpb30Days()
    {
        $results = DB::select(DB::raw("
            WITH date_series AS (
                SELECT generate_series(CURRENT_DATE - INTERVAL '29 days', CURRENT_DATE, '1 day'::interval) AS date
            )
            SELECT
                ds.date,
                COALESCE(SUM(CASE WHEN lpb.spb_status = 1 THEN lpb_items.qty END), 0) AS total_out_qty,
                COALESCE(SUM(lpb_items.qty), 0) AS total_in_qty
            FROM
                date_series ds
            LEFT JOIN
                lpb ON DATE(lpb.created_at) = ds.date AND lpb.status IN (1, 2)
            LEFT JOIN
                lpb_items ON lpb.id = lpb_items.lpb_id
            GROUP BY
                ds.date
            ORDER BY
                ds.date ASC
        "));
        return $results;
    }

}
