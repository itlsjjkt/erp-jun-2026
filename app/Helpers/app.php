<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\User;
use Illuminate\Support\Facades\Gate;


function getStatusDPM($status, $raw = null, $hold = null)
{
	if ($status == 1) {
		if ($raw == null) {
			if($hold == 3) return "<span class='badge badge-warning'>Hold DPM</span>";
			else if($hold == 11) return "<span class='badge badge-warning'>On Progress</span>";
			else return "<span class='badge badge-info'>On Progress Approval</span>";
		} else {
			if($hold == 3) return "Hold DPM";
			else if($hold == 11) return "On Progress DPM";
			else return "On Progress Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Rejected</span>";
		} else {
			return "Rejected";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Hold</span>";
		} else {
			return "Hold";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-info'>PR Issued</span>";
		} else {
			return "PR Issued";
		}
	} else if ($status == 5) {
		if ($raw == null) {
			return "<span class='badge badge-success'>PO Issued</span>";
		} else {
			return "PO Issued";
		}
	}
    // DRAFT DPM
    else if ($status == 11) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Draft DPM</span>";
		} else {
			return "Draft DPM";
		}
	}
    else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}


function getStatusItemDPM($status, $statusPR = null, $statusPO = null, $parsial = null, $statusDPM = null, $typeDPM =null, $raw = null)
{
	if ($status == 1) {
		if ($statusDPM == 3 ) {
			if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
			else return "Hold";
		} else if($statusDPM == 11){
            if ($raw == null) return "<span class='badge badge-warning'>On Progress</span>";
			else return "On Progress";
        }
		else {
			if ($raw == null)return "<span class='badge badge-info'>On Progress Approval</span>";
		 	else return "On Progress Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
		else return "Rejected";
	} else if ($status == 3) {
		if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
		else return "Cancel";
	} else if ($status == 4) {
		if ($statusPR == 1 && $statusPO == 0) {
			if ($raw == null) return "<span class='badge badge-primary'>PR Issued</span>";
			else return "PR Issued";
		}
		if ($statusPR == 1 && $statusPO == 1) {
			if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDPM)." Issued</span>";
			else return strtoupper($typeDPM)." Issued";
		}
		if ($statusPR == 1 && $statusPO == 2) {
			if ($raw == null)return "<span class='badge badge-purple'>PR Parsial</span>";
			else return "PR Parsial";
		}
		if ($statusPR == 1 && $statusPO == 3) {
			if ($parsial == 0) {
				if ($raw == null) return "<span class='badge badge-primary'>PR Closed</span>";
				else return "PR Closed";
			} else {
				if ($raw == null) return "<span class='badge badge-success'>PO Issued</span>";
				else return "PO Issued";
			}
		}
	} else if ($status == 5) {
		if ($raw == null) return "<span class='badge badge-success'>PO Issued</span>";
		else return "PO Issued";
	} else {
		if ($raw == null)  return "<span class='badge badge-warning'>Draft</span>";
	 	else return "Draft";
	}
}



function getStatusItemExportDPM($status, $statusPR = null, $statusPO = null, $parsial = null, $lpb_status = null, $lpb_parsial = null, $spb_status = null, $bpb_status = null)
{
	if ($status == 1) {
		return "On Progress";
	} else if ($status == 2) {
		return "Rejected";
	} else if ($status == 3) {
		return "Cancel";
	} else if ($status == 4) {
		if ($statusPR == 1 && $statusPO == 0) {
			return "PR Issued";
		}
		if ($statusPR == 1 && $statusPO == 1) {
			if ($lpb_status == 1) {
				if ($spb_status == 1 && $bpb_status == 1) {
					return "SPB Issued";
				} else if ($spb_status == 1 && $bpb_status == 2) {
					return "BPB Parsial";
				} else if ($spb_status == 1 && $bpb_status == 3) {
					return "BPB Done";
				} else {
					return "LPB Issued";
				}
			} else if ($lpb_status == 2) {
				return "Parsial";
			} else {
				return "PO Issued";
			}
		}
		if ($statusPR == 1 && $statusPO == 2) {
			return "PR Parsial";
		}
		if ($statusPR == 1 && $statusPO == 3) {
			if ($parsial == 0) {
				return "PR Closed";
			} else {
				return "PO Issued";
			}
		}
	} else {
		return "Draft";
	}
}

function getStatusItemPR($statusPR, $statusPO, $parsial, $type = null, $raw = null)
{
	if ($type == 'po') {
		if ($statusPR == 1 && $statusPO == 0) {
			if ($raw == null) {
				return "<span class='badge badge-primary'>Belum PO</span>";
			} else {
				return "Belum PO";
			}
		}
		if ($statusPR == 1 && $statusPO == 1) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Sudah PO</span>";
			} else {
				return "Sudah PO";
			}
		}
		if ($statusPR == 1 && $statusPO == 2) {
			if ($raw == null) {
				return "<span class='badge badge-primary'>Parsial PO</span>";
			} else {
				return "Parsial PO";
			}
		}
		if ($statusPR == 1 && $statusPO == 3) {
			if ($parsial == 0) {
				if ($raw == null) {
					return "<span class='badge badge-danger'>Closed</span>";
				} else {
					return "Closed";
				}
			} else {
				if ($raw == null) {
					return "<span class='badge badge-primary'>Sudah PO Parsial</span>";
				} else {
					return "Sudah PO Parsial";
				}
			}
		}
		if ($statusPR == 1 && $statusPO == 4) {
			if ($raw == null) {
				return "<span class='badge badge-primary'>Closed Parsial</span>";
			} else {
				return "Closed Parsial";
			}
		}

	}else{

		if ($type == 'im') {
            if($statusPO == 3){
                if ($raw == null) {
                    return "<span class='badge badge-danger'>Closed IM</span>";
                } else {
                    return "Closed IM";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-primary'>IM</span>";
                } else {
                    return "IM";
                }
            }
		}
        if ($type == 'petty_cash') {
            if($statusPO == 3){
                if ($raw == null) {
                    return "<span class='badge badge-danger'>Closed PETTY CASH</span>";
                } else {
                    return "Closed PETTY CASH";
                }
            }else{
                if ($statusPR == 1 && $statusPO == 1) {
                    if ($raw == null) {
                        return "<span class='badge badge-success'>Petty Cash</span>";
                    } else {
                        return "Petty Cash";
                    }
                }
            }
		}
	}
}

function getStatusPR($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress</span>";
		} else {
			return "On Progress";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>PR Parsial</span>";
		} else {
			return "PR Parsial";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-purple'>Reject PR</span>";
		} else {
			return "Reject PR";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else if ($status == 5) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Closed</span>";
		} else {
			return "Closed";
		}
	} else if ($status == 6) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Closed Parsial</span>";
		} else {
			return "Closed Parsial";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Elevated to PO</span>";
		} else {
			return "Elevated to PO";
		}
	}
}

function getStatusPO($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress Approval</span>";
		} else {
			return "On Progress Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>PO Issued</span>";
		} else {
			return "PO Issued";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-purple'>Perbaikan</span>";
		} else {
			return "Perbaikan";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Parsial</span>";
		} else {
			return "Parsial";
		}
	} else if ($status == 5) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else if ($status == 6) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Cancel</span>";
		} else {
			return "Cancel";
		}
	}
	else if ($status == 8) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Revised</span>";
		} else {
			return "Revised";
		}
	} else if ($status == 9) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Revision Draft</span>";
		} else {
			return "Revision Draft";
		}
	} else if ($status == 10) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>PO Draft</span>";
		} else {
			return "PO Draft";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

function getStatusTransferInventory($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Menunggu Approval</span>";
		} else {
			return "Menunggu Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Disetujui</span>";
		} else {
			return "Disetujui";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Diterima</span>";
		} else {
			return "Diterima";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge' style='background: #a207ff;color:#fff'>Parsial</span>";
		} else {
			return "Parsial";
		}
	} else if ($status == 5) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Selesai</span>";
		} else {
			return "Selesai";
		}
	} else if ($status == 6) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Ditolak</span>";
		} else {
			return "Ditolak";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

function getStatusSPB($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Published</span>";
		} else {
			return "Published";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge' style='background: #f5ab35;color:#fff'>Parsial</span>";
		} else {
			return "Parsial";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Reversal</span>";
		} else {
			return "Reversal";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}


function getStatusPOLPB($status, $raw = null)
{
	if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Parsial</span>";
		} else {
			return "Parsial";
		}
	} else {
		return '';
	}
}

function getStatusPOArray($status)
{


	$value = '';
	if ($status == 1) {
		$value .= "<span class='mr-2'>On Progress</span>";
	} else if ($status == 2) {
		$value .= "<span class='mr-2'>PO Issued</span>";
	} else if ($status == 3) {
		$value .= "<span class='mr-2'>Perbaikan</span>";
	} else if ($status == 4) {
		$value .= "<span class='mr-2'>Parsial</span>";
	} else if ($status == 5) {
		$value .= "<span class='mr-2'>Done</span>";
	} else {
		$value .= "<span class='mr-2'>Draft</span>";
	}
	return $value;
}


function getStatusData($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Published</span>";
		} else {
			return "Published";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

function getStatusLPB($status,$spb_status, $raw = null)
{
	if ($status == 1) {
        if($spb_status == 1){
            if ($raw == null) {
                return "<span class='badge badge-success'>Done</span>";
            } else {
                return "Done";
            }
        }else if($spb_status == 2){
            if ($raw == null) {
                return "<span class='badge badge-primary'>Parsial SPB</span>";
            } else {
                return "Parsial SPB";
            }
        }else{
            if ($raw == null) {
                return "<span class='badge badge-info'>On Progress</span>";
            } else {
                return "On Progress";
            }
        }
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Parsial SPB</span>";
		} else {
			return "Parsial SPB";
		}
    }else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Close</span>";
		} else {
			return "Close";
		}
    }else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Reversal</span>";
		} else {
			return "Reversal";
		}
    }  else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}


function getStatusDataTTB($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null)  return "<span class='badge badge-info'>Published</span>";
		else return "Published";
	} else if ($status == 2) {
		if ($raw == null)  return "<span class='badge badge-danger'>Reversal</span>";
		else return "Reversal";
	} else {
		if ($raw == null) return "<span class='badge badge-warning'>Draft</span>";
		else return "Draft";
	}
}

function getStatusDataROT($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Menunggu Pembuatan RIN </span>";
		} else {
			return "Menunggu Pembuatan RIN";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Return Out Parsial</span>";
		} else {
			return "Return Out Parsial";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

function getStatusDataRIN($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Menunggu Approval Site</span>";
		} else {
			return "Menunggu Approval Site";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Parsial RIN</span>";
		} else {
			return "Parsial RIN";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}



function getNotifications($user_id)
{
	$query = DB::table('notifications')
		->select('*')
		->where('user_id', $user_id)
		->where('status', 0)
		->orderBy('created_at','DESC')
		->get();
	return $query;
}

function getApprovalLogistic($location, $step)
{
	$approval = DB::table('approval_logistics')
		->select('users.email', 'users.name', 'users.notification_email', 'approval_logistics.user_id')
		->leftJoin('users', 'users.id', '=', 'approval_logistics.user_id')
		->where('approval_logistics.location_id', $location)
		->where('approval_logistics.step', $step)
		->first();
	return $approval;
}


function getApprovalPurchasing($company, $step)
{
	$approval = DB::table('approval_purchasings')
		->select('users.email', 'users.name', 'users.notification_email', 'approval_purchasings.user_id')
		->leftJoin('users', 'users.id', '=', 'approval_purchasings.user_id')
		->where('approval_purchasings.company_id', $company)
		->where('approval_purchasings.step', $step)
		->first();
	return $approval;
}

function getAllApprovalDph($company)
{
	$approval = DB::table('approval_dph')
		->select('users.email', 'users.name', 'users.notification_email', 'approval_dph.user_id')
		->leftJoin('users', 'users.id', '=', 'approval_dph.user_id')
		->where('approval_dph.company_id', $company)
		->get();
	return $approval;
}

function getApprovalDph($company, $step)
{
	$approval = DB::table('approval_dph')
		->select('users.email', 'users.name', 'users.notification_email', 'approval_dph.user_id')
		->leftJoin('users', 'users.id', '=', 'approval_dph.user_id')
		->where('approval_dph.company_id', $company)
		->where('approval_dph.step', $step)
		->first();
	return $approval;
}

function getDPMLog($id)
{
	$query = DB::table('purchase_notes')
		->select('pr_item_id')
		->where('pr_item_id', $id)
		->where('message', '!=', '')
		->get()
		->count();
	return $query;
}

function getPurchaser()
{
	$users = DB::table('users')->where('type', 4)->where('data_access', NULL)->get();
	return $users;
}

function getAdminPurchasing()
{
	$users = DB::table('users')->where('type', 4)->where('data_access', 1)->get();
	return $users;
}

function getAllApprovalPurchasing($company)
{
	$approval = DB::table('approval_purchasings')
		->select('users.id AS user_id', 'users.email', 'approval_purchasings.*')
		->leftJoin('users', 'users.id', '=', 'approval_purchasings.user_id')
		->where('approval_purchasings.company_id', $company)
		->get();
	return $approval;
}

function getAllApprovalLogistic($location)
{
	$approval = DB::table('approval_logistics')
		->select('users.id AS user_id', 'users.email', 'users.name')
		->leftJoin('users', 'users.id', '=', 'approval_logistics.user_id')
		->where('approval_logistics.location_id', $location)
		->get();
	return $approval;
}

function getNextApprovalDPM($location, $step)
{
	$approval = DB::table('approval_logistics')
		->select('users.name')
		->leftJoin('users', 'users.id', '=', 'approval_logistics.user_id')
		->where('approval_logistics.location_id', $location)
		->where('approval_logistics.step', $step)
		->first();
	if ($approval) {
		return $approval->name;
	}
}


function getUserByID($id)
{
	$users = DB::table('users')
		->select('users.*')
		->where('id', $id)
		->first();
	if ($users) {
		return $users->name;
	} else {
		return "-";
	}
}


function getProductType($code)
{
	$query = DB::table('master_item_types')
		->select('code')
		->where('product_id', $code)
		->get()->toArray();
	if ($query) {
		$output = array_map(function ($object) {
			return $object->code;
		}, $query);
		$code = implode(', ', $output);
		return $code;
	} else {
		return "-";
	}
}


function getDataByID($table, $id)
{
	$data = DB::table($table)
		->select('*')
		->where('id', $id)
		->first();
	return $data;
}


function checkParsialPR($pr_id){
	$data = DB::table('purchase_items')
	->select('*')
	->where('pr_status', 1)
	->where('pr_id', $pr_id)
	->whereIn('po_status', [0,2])
	->get();

	if(count($data)) return true;
	else return false;
}

function checkParsialClosePR($pr_id){
    $data = DB::table('purchase_items')
        ->select('*')
        ->where('pr_status', 1)
        ->where('pr_id', $pr_id)
        ->whereIn('po_status', [1, 3])
        ->get();

    $hasPoStatus3 = $data->contains('po_status', 3);

    $validPoStatus = $data->every(function($item) {
        return $item->po_status == 1 || $item->po_status == 3;
    });

    if ($hasPoStatus3 && $validPoStatus) {
        return true;
    }else{
		return false;
	}
}


function getDataByIDParam($table, $param, $value)
{
	$data = DB::table($table)
		->select('*')
		->where($param, $value)
		->first();
	return $data;
}


function getDataWhereIn($table, $param, $value)
{
	$data = DB::table($table)
		->select('*')
		->whereIn($param, $value)
		->get();
	return $data;
}

function getDPMItemCategory($id)
{
	$query = DB::table('purchase_items')
		->select('master_items.name')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
		->leftJoin('master_items', 'master_item_products.item_id', '=', 'master_items.id')
		->where('purchase_items.purchase_id', $id)
		->get();
	if ($query) {
		$cat = [];
		foreach ($query as $val) {
			$cat[] = $val->name;
		}
		$code = implode(', ', array_unique($cat));
		return $code;
	} else {
		return "-";
	}
}

function getDataUsersByID($id)
{
	$data = DB::table('users')
		->select('*')
		->whereIn('id', $id)
		->get();
	return $data;
}

function getPRItem($id)
{

	$query = DB::table('purchase_items')
		->select('purchase_items.*',
		'users.name AS purchaser',
		'master_item_products.name AS product',
		'master_item_products.code AS productCode',
		'master_item_products.part_number AS productPartNumber',
		'master_item_brands.name AS productBrand')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
		->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
		->leftJoin('users', 'users.id', '=', 'purchase_items.assigned_id')
		->where('purchase_items.pr_id', $id)
		->where('purchase_items.pr_status', 1)
        ->orderBy('id','ASC')
		->get();

	return $query;
}

function getPOItem($id)
{

	$query = DB::table('po_items')
		->select('po_items.*', 'purchase_items.qty_parsial', 'purchase_items.qty AS qty_pr', 'purchase_items.po_status', 'master_item_products.name AS product', 'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber', 'master_item_brands.name AS productBrand')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
		->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->where('po_items.po_id', $id)
        ->orderBy('pr_item_id','ASC')
		->get();
	return $query;
}


function getLPBItem($id)
{
	$query = DB::table('lpb_items')
		->select(
			'lpb_items.*',
			'po_items.qty as qtyPO',
			'po_items.price as price',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'po_items.measure',
			'master_item_brands.name AS productBrand',
			'lpb.location_id',
			'currencies.name as currencies_name',
			'currencies.conversion_idr as conversion_idr'
		)
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('po', 'po.id', '=', 'po_items.po_id')
		->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
		->where('lpb_items.lpb_id', $id)
		->whereIn('lpb_items.status', [0,2])
        ->orderBy('po_item_id','ASC')
		->get();
	return $query;
}

function getLPBItemMonitor($id)
{
	$query = DB::table('lpb_items')
		->select(
			'lpb_items.*',
			'po_items.qty as qtyPO',
			'po_items.price as price',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'po_items.measure',
			'master_item_brands.name AS productBrand',
			'lpb.location_id',
			'currencies.name as currencies_name',
			'currencies.conversion_idr as conversion_idr'
		)
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('po', 'po.id', '=', 'po_items.po_id')
		->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
		->where('lpb_items.lpb_id', $id)
        ->orderBy('po_item_id','ASC')
		->get();
	return $query;
}

function getLPBbySPBID($ids)
{
	$query = DB::table('lpb')
		->select('lpb.doc_no', 'po.doc_no AS po_no', 'purchase_requisitions.doc_no as pr_no', 'purchase_requisitions.dpm_no as dpm_no')
		->leftJoin('po', 'po.id', '=', 'lpb.po_id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->whereIn('lpb.id', $ids)
		->get();
	return $query;
}


function getSPBItem($id)
{
	$query = DB::table('spb')
		->select(
			'spb.*',
			'lpb_items.product_id',
			'lpb_items.qty',
			'po_items.measure',
			'po_items.qty as qtyPO',
			'po_items.price as price',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand'
		)
		->leftJoin('spb_kolis', 'spb.id', '=', 'spb_kolis.spb_id')
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
		->where('spb_kolis.spb_id', $id)
		->where('spb_kolis.status', 1)
		->get();
	return $query;
}
function getItemSPB($id)
{
	$query = DB::table('spb_kolis')
		->select(
			'spb_kolis.*',
			'suppliers.name AS supplier',
			'po.doc_no AS noPO',
			'purchase_requisitions.dpm_no AS noDPM',
			'departments.name AS department',
			'lpb_items.qty AS qtyLpb',
			'master_item_products.id AS productID',
			'po_items.qty as qtyPO',
			'po_items.price as price',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure',
			'currencies.name as currencies_name',
			'currencies.conversion_idr as conversion_idr'
		)
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('currencies', 'currencies.name', '=', 'po.currency')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('spb_kolis.lpb_id', $id)
		->get();
	return $query;
}

function getPRWithLPBisPublish($doc_no)
{

	$query =  DB::table('purchase_requisitions')
		->select('purchase_requisitions.id', 'lpb.status as lpb_status')
		->leftJoin('po', 'po.purchase_id', '=', 'purchase_requisitions.id')
		->leftJoin('lpb', 'lpb.id', '=', 'po.id')
		//->distinct('purchase_requisitions.id')
		->where('purchase_requisitions.doc_no', $doc_no)
		->first();

	return $query;
}


function getSPBKoli($id)
{

	$query = DB::table('spb_kolis')
		->select(
			'spb_kolis.id AS idKoli',
			'spb_kolis.qty AS qtyKoli',
			'spb_kolis.annotation',
			'suppliers.name AS supplier',
			'po.doc_no AS noPO',
			'purchase_requisitions.dpm_no AS noDPM',
			'lpb.doc_no AS noLPB',
			'lpb_items.qty AS qty',
			'po_items.price as price',
			'master_item_products.name AS product',
			'master_item_products.id AS productID',
			'departments.name AS department',
			'po_items.specification',
			'suppliers.id AS supplierID',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('spb_kolis.spb_id', $id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}


function getKoli($id)
{

	$query = DB::table('spb_kolis')
		->where('spb_item_id', $id)
		->orderBy('no', 'ASC')
		->get();
	return $query;
}


function getItemBPB($id)
{
	$query = DB::table('bpb_items')
		->select(
			'spb_kolis.*',
			'po.doc_no AS noPO',
			'purchase_requisitions.dpm_no AS noDPM',
			'lpb_items.qty AS qty',
			'master_item_products.id AS productID',
			'po_items.qty as qtyPO',
			'po_items.price as price',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('spb_kolis', 'spb_kolis.id', '=', 'bpb_items.spb_item_id')
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('bpb_items.bpb_id', $id)
		->get();
	return $query;
}


function getBPBKoli($id)
{

	$query = DB::table('bpb_items')
		->select(
			'bpb_items.*',
			'spb_kolis.id AS idKoli',
			'spb_kolis.qty AS qtyKoli',
			'spb_kolis.annotation',
			'po.doc_no AS noPO',
			'purchase_requisitions.doc_no AS noPR',
			'purchase_requisitions.dpm_no AS noDPM',
			'spb.doc_no AS noSPB',
			'lpb.doc_no AS noLPB',
			'master_item_products.id AS product_id',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->where('bpb_items.bpb_id', $id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}

function getSPBInsurance($id, $type)
{

	$query = DB::table('spb_kolis')
		->select(
			'spb_kolis.id AS idKoli',
			'spb_kolis.qty AS qtyKoli',
			'spb_kolis.annotation',
			'suppliers.name AS supplier',
			'po.doc_no AS noPO',
			'purchase_requisitions.dpm_no AS noDPM',
			'lpb.doc_no AS noLPB',
			'lpb_items.qty AS qty',
			'po_items.price as price',
			'master_item_products.name AS product',
			'master_item_products.id AS productID',
			'departments.name AS department',
			'po_items.specification',
			'suppliers.id AS supplierID',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('spb_kolis.spb_id', $id)
		->where('spb_kolis.status_insurance_' . $type, 0)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}

function getSupplierByLPB($id)
{
	$id = explode(',', $id);
	$query = DB::table('suppliers')
		->select(
			'suppliers.*',
			'supplier_contacts.name AS supplierPIC',
			'supplier_contacts.telp AS supplierTelp',
			'supplier_contacts.email AS supplierEmail',
		)
		->leftJoin('po', 'po.supplier_id', '=', 'suppliers.id')
		->leftJoin('supplier_contacts', 'po.supplier_contact_id', '=', 'supplier_contacts.id')
		->leftJoin('lpb', 'po.id', '=', 'lpb.po_id')
		->whereIn('lpb.id', $id)
		->first();

	return $query;
}

function getVendor($id)
{
	$query = DB::table('suppliers')
		->select('suppliers.*')
		->where('id', $id)
		->first();

	return $query;
}

function isAdministrator()
{
	if (Auth::user()->type == 1) {
		return true;
	} else {
		return false;
	}
}

function isAdministratorCompany()
{
	if (Auth::user()->type == 2) {
		return true;
	} else {
		return false;
	}
}

function isAdministratorLocation()
{
	if (Auth::user()->type == 3) {
		return true;
	} else {
		return false;
	}
}

function isPurchasing()
{
	if (Auth::user()->type == 4) {
		return true;
	} else {
		return false;
	}
}

function isSuperAdmin()
{
	if (Auth::user()->id == 1) {
		return true;
	} else {
		return false;
	}
}

function isPoPriceAccess()
{
	if (GATE::allows('purchase_order_price')) {
		return true;
	} else {
		return false;
	}
}

function isEmployee()
{
	if (Auth::user()->type == 5) {
		return true;
	} else {
		return false;
	}
}

function isEmployeeAdministrator()
{
	if (Auth::user()->type == 5 && Auth::user()->data_access == 1) {
		return true;
	} else {
		return false;
	}
}

function isLocationAdministrator()
{
	if (Auth::user()->type == 3 && Auth::user()->data_access == 1) {
		return true;
	} else {
		return false;
	}
}

function isAdmin()
{
	if (Auth::user()->type == 6) {
		return true;
	} else {
		return false;
	}
}

function getPaymentMethod($payment)
{
	if ($payment == 1) {
		return "CASH";
	} else if ($payment == 2) {
		return "TRANSFER";
	} else if ($payment == 3) {
		return "CHEQUE";
	} else if ($payment == 4) {
		return "GIRO";
	} else if ($payment == 5) {
		return "LC";
	} else {
		return "TT";
	}
}


function getCurrencySymbol($code)
{
	$code = trim($code);
	$query = DB::table('currencies')
		->select('symbol')
		->where('name', $code)
		->first();
	if ($query) return $query->symbol;
	else return "-";
}


function getStatusInventory($max, $min, $onhand, $raw = null)
{
	if ($max == $onhand) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Max Stock</span>";
		} else {
			return "Max Stock";
		}
	} else if ($onhand >= $max) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Over Stock</span>";
		} else {
			return "Over Stock";
		}
	} else if ($onhand <= $min) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Urgent Order</span>";
		} else {
			return "Urgent Order";
		}
	} else if ($onhand < $max && $onhand > $min) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Safe Stock</span>";
		} else {
			return "Safe Stock";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>-</span>";
		} else {
			return "-";
		}
	}
}

function statusInventory($status, $raw = null)
{
	if ($status == "Max Stock") {
		if ($raw == null) {
			return "<span class='badge badge-info'>Max Stock</span>";
		} else {
			return "Max Stock";
		}
	} else if ($status == "Over Stock") {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Over Stock</span>";
		} else {
			return "Over Stock";
		}
	} else if ($status == "Urgent Order") {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Urgent Order</span>";
		} else {
			return "Urgent Order";
		}
	} else if ($status == "Safe Stock") {
		if ($raw == null) {
			return "<span class='badge badge-success'>Safe Stock</span>";
		} else {
			return "Safe Stock";
		}
	} else {
		return "-";
	}
}


function cekDPM($id)
{

	$query = DB::table('purchase_items')
		->select('purchase_items.id')
		->where('purchase_items.purchase_id', $id)
		->where('purchase_items.status', 1)
		->get()
		->count();
	return $query;
}

function group_by($key, $data)
{
	$result = array();

	foreach ($data as $val) {
		if (array_key_exists($key, $val)) {
			$result[$val[$key]][] = $val;
		} else {
			$result[""][] = $val;
		}
	}

	return $result;
}

function getChecked($val, $arr)
{
	if ($val == $arr) {
		echo 'checked';
	}
}

function numberPrecision($number, $decimals = 0)
{
    $negation = ($number < 0) ? (-1) : 1;
    $coefficient = 10 ** $decimals;
    return $negation * floor((string)(abs($number) * $coefficient)) / $coefficient;
}

function format_number($val)
{
	return number_format($val, 2, ",", '.');
}

function getPOItemByDPM($id)
{

	$query = DB::table('po_items')
		->select('po_items.qty', 'po_items.price', 'po_items.discount', 'po.doc_no', 'po.publish', 'po.ppn', 'suppliers.name AS supplier', 'users.name AS purchaser')
		->leftJoin('po', 'po.id', '=', 'po_items.po_id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('users', 'po.created_by', '=', 'users.id')
		->where('po_items.pr_item_id', $id)
		->where('po.status', '!=', 0)
		->get();
	return $query;
}

function getLPBItemByDPM($id)
{

	$query = DB::table('lpb_items')
		->select('lpb_items.qty', 'lpb.doc_no', 'lpb.publish')
		->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
		->where('lpb_items.pr_item_id', $id)
		->get();
	return $query;
}

function getSPBItemByDPM($id)
{

	$sql = "SELECT t1.doc_no, t1.publish, t2.qty
	FROM spb t1
	INNER JOIN
		(SELECT spb_id, COALESCE(SUM(spb_kolis.qty),0) AS qty
		FROM  spb_kolis
        WHERE spb_kolis.pr_item_id =  $id
		GROUP BY spb_id
		) t2
	ON t1.id = t2.spb_id
	";

	return DB::select($sql);
}


function getBPBItemByDPM($id)
{

	$sql = "SELECT
	t1.doc_no, t1.publish, t2.qty
	FROM bpb t1
	INNER JOIN
		(SELECT bpb_id, COALESCE(SUM(bpb_items.qty),0) AS qty
		FROM bpb_items
        WHERE bpb_items.pr_item_id =  $id
		GROUP BY bpb_id
		) t2
	ON t1.id = t2.bpb_id
	";

	return DB::select($sql);
}


function getSPBKoliByLPB($spb_id,$lpb_id)
{

	$query = DB::table('spb_kolis')
		->select(
			'spb_kolis.id AS idKoli',
			'spb_kolis.annotation',
			'spb_kolis.qty AS qtyKoli',
			'suppliers.name AS supplier',
			'po.doc_no AS noPO',
			'purchase_requisitions.doc_no AS noPR',
			'lpb.doc_no AS noLPB',
			'lpb_items.qty AS qty',
			'po_items.price as price',
			'master_item_products.name AS product',
			'master_item_products.id AS productID',
			'departments.name AS department',
			'po_items.specification',
			'suppliers.id AS supplierID',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('spb_kolis.spb_id', $spb_id)
		->where('spb_kolis.lpb_id', $lpb_id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}

function getSPBKoliByDPM($spb_id,$dpm_id)
{

	$query = DB::table('spb_kolis')
		->select(
			'spb_kolis.lpb_id as lpb_idd',
			'spb_kolis.id AS idKoli',
			'spb_kolis.annotation',
			'spb_kolis.qty AS qtyKoli',
			'suppliers.name AS supplier',
			'po.doc_no AS noPO',
			'purchase_requisitions.doc_no AS noPR',
			'lpb.doc_no AS noLPB',
			'lpb_items.qty AS qty',
			'po_items.price as price',
			'master_item_products.name AS product',
			'master_item_products.id AS productID',
			'departments.name AS department',
			'po_items.specification',
			'suppliers.id AS supplierID',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->where('spb_kolis.spb_id', $spb_id)
		->where('purchase_requisitions.purchase_id', $dpm_id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}


function getBPBKoliByLPB($id)
{

	$query = DB::table('bpb_items')
		->select(
			'bpb_items.*',
			'spb_kolis.id AS idKoli',
			'spb_kolis.qty AS qtyKoli',
			'spb_kolis.annotation',
			'po.doc_no AS noPO',
			'purchase_requisitions.doc_no AS noPR',
			'purchase_requisitions.dpm_no AS noDPM',
			'spb.doc_no AS noSPB',
			'lpb.doc_no AS noLPB',
			'master_item_products.id AS product_id',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('spb', 'spb_kolis.spb_id', '=', 'spb.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->where('bpb_items.spb_item_id', $id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();
	return $query;
}


function getBPBItemByID($id, $bpb_id)
{

	$query = DB::table('bpb_items')
		->select(
			'bpb_items.*',
			'spb_kolis.id AS idKoli',
			'spb_kolis.qty AS qtyKoli',
			'spb_kolis.annotation',
			'po.doc_no AS noPO',
			'purchase_requisitions.doc_no AS noPR',
			'purchase_requisitions.dpm_no AS noDPM',
			'lpb.doc_no AS noLPB',
			'master_item_products.id AS product_id',
			'master_item_products.name AS product',
			'po_items.specification',
			'master_item_products.code AS productCode',
			'master_item_products.part_number AS productPartNumber',
			'master_item_brands.name AS productBrand',
			'po_items.measure'
		)
		->leftJoin('spb_kolis', 'bpb_items.spb_item_id', '=', 'spb_kolis.id')
		->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
		->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
		->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
		->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
		->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
		->leftJoin('po', 'po_items.po_id', '=', 'po.id')
		->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
		->where('bpb_items.spb_item_id', $id)
		->where('bpb_items.bpb_id', $bpb_id)
		->orderBy('spb_kolis.no', 'ASC')
		->get();

	return $query;
}

function currencyRupiahFormat($price)
{
	$rupiahFormat = number_format($price, 2, ',', '.');
	return $rupiahFormat;
}

function getStatusLibraryOnline($status, $raw = null)
{
	if ($status == 0) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Waiting Approval</span>";
		} else {
			return "Waiting Approval";
		}
	} else if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>On Progress Approval</span>";
		} else {
			return "On Progress Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Rejected</span>";
		} else {
			return "Rejected";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Accepted</span>";
		} else {
			return "Accepted";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Unknown</span>";
		} else {
			return "unknown";
		}
	}
}

function getStatusLeave($status, $raw = null)
{
	if ($status == 0) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	} else if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Publish (Waiting Approval)</span>";
		} else {
			return "Publish (Waiting Approval)";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress Approval</span>";
		} else {
			return "On Progress Approval";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Rejected</span>";
		} else {
			return "Rejected";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Accepted</span>";
		} else {
			return "Accepted";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Unknown</span>";
		} else {
			return "unknown";
		}
	}
}

function getStatusSPPD($status, $raw = null)
{
	if ($status == 0) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	} else if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>Publish (Waiting Approval)</span>";
		} else {
			return "Publish (Waiting Approval)";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress Approval</span>";
		} else {
			return "On Progress Approval";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Rejected</span>";
		} else {
			return "Rejected";
		}
	} else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Accepted</span>";
		} else {
			return "Accepted";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Unknown</span>";
		} else {
			return "unknown";
		}
	}
}

function getStock($product_id, $location_id)
{
	$sqlStock = "SELECT a.stock_onhand as stock_onhand, a.stock_min as stock_min, a.stock_max as stock_max, a.updated_at as updated_at, a.location_id as location_id FROM inventories as a WHERE a.product_id = '$product_id'" . " and a.location_id = '" . $location_id . "' ORDER BY updated_at DESC LIMIT 1";
	return DB::select(DB::raw($sqlStock));
}

function getStockProductType($product_id, $location_id)
{
	$sqlStock = "SELECT
	a.stock_onhand as stock_onhand,
 	a.stock_min as stock_min,
	a.stock_max as stock_max,
	a.updated_at as updated_at,
	a.location_id as location_id
	FROM inventories as a
	WHERE a.product_id = '$product_id'" . "
	and a.location_id = '" . $location_id . "'
	ORDER BY updated_at DESC LIMIT 1";
	return DB::select(DB::raw($sqlStock));
}


function strleft($str, $separator)
{
	if (intval($separator)) {
		return substr($str, 0, $separator);
	} elseif ($separator === 0) {
		return $str;
	} else {
		$strpos = strpos($str, $separator);
		if ($strpos === false) {
			return $str;
		} else {
			return substr($str, 0, $strpos);
		}
	}
}

function strright($str, $separator)
{
	if (intval($separator)) {
		return substr($str, -$separator);
	} elseif ($separator === 0) {
		return $str;
	} else {
		$strpos = strpos($str, $separator);
		if ($strpos === false) {
			return $str;
		} else {
			return substr($str, -$strpos + 1);
		}
	}
}

function cleanFileName($string)
{
	return preg_replace('/[^A-Za-z0-9. -]/', '_', $string); // Remove all characters except A-Z, a-z, 0-9, dots, hyphens and spaces
}

function agingInventory($count)
{
	$result = "";
	if ($count >= 365) {
		$days = ($count % 365)  % 30;
		$months = floor(($count % 365) / 30);
		$years = floor($count / 365);
		$result = $years . " tahun " . $months . " bulan " . $days . " hari";
	} else if ($count > 0 && $count < 365) {
		$days = $count % 30;
		$months = ($count - $days) / 30;
		$result = $months . " bulan " . $days . " hari";
	} else if ($count == 0) {
		$result = "0 hari";
	}
	return $result;
}

function getCodeRankProductItem($product_id, $location_id)
{
	$inv  = DB::table('inventories')->where('product_id', $product_id)->where('location_id', $location_id)->first();
	return $inv;
}

function isWeekend($date)
{
	$check = (date('N', strtotime($date)) >= 6);
	if ($check) {
		return true;
	} else {
		return false;
	}
}


function getUserType($status)
{
	if ($status == 1) {
		return "<span class='badge badge-info'>Super Administrator</span>";
	} else if ($status == 2) {
		return "<span class='badge badge-success'>Company</span>";
	} else if ($status == 3) {
		return "<span class='badge badge-danger'>Warehouse</span>";
	} else if ($status == 4) {
		return "<span class='badge badge-warning'>Purchasing</span>";
	} else if ($status == 6) {
		return "<span class='badge badge-primary'>Administrator</span>";
	} else {
		return "<span class='badge badge-default'>Employee</span>";
	}
}


function has_dupes($array)
{
	$dupe_array = array();
	foreach ($array as $val) {
		if (++$dupe_array[$val] > 1) {
			return true;
		}
	}
	return false;
}



function getProductFrancoItem($id){

	$query = DB::table('bpb_items')
	  ->select('bpb_items.*',
	   'po_items.id AS idPO',
	   'po_items.qty AS qtyPO',
	   'po_items.lpb_status',
	   'po_items.qty_parsial',
	   'po_items.price',
	   'po_items.discount AS price_discount',
	   'po_items.measure',
	   'master_item_products.id AS product_id',
	   'po_items.specification',
	   'master_item_products.name AS product',
	   'master_item_products.code AS productCode',
	   'master_item_products.part_number AS productPartNumber',
	   'master_item_products.measure_inventory AS productMeasure',
	   'master_item_products.conversion AS productConversion',
	   'master_item_brands.name AS productBrand',
	   'purchase_requisitions.doc_no AS noPR',
	   'purchase_requisitions.dpm_no AS noDPM',
	   'purchase_requisitions.location_id',
	  )
	  ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
	  ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
	  ->leftJoin('master_item_products', 'master_item_products.id', '=', 'purchase_items.product_id')
	  ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	  ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'purchase_items.pr_id')
	  ->where('bpb_items.bpb_id', $id)
	  ->orderBy('idPO', 'ASC')
	  ->get();
	 return $query;


 }

function getProductItemSPBInsurance($id){

	$query = DB::table('spb_kolis')
	  ->select(
		  DB::raw('cast(spb_kolis.no AS INT) as no_koli'),
		  'spb_kolis.id AS idKoli',
		  'spb_kolis.qty AS qtyKoli',
		  'spb_kolis.uuid AS uuid',
		  'spb_kolis.annotation',
		  'suppliers.name AS supplier',
		  'po.doc_no AS noPO',
		  'purchase_requisitions.dpm_no AS noDPM',
		  'lpb.doc_no AS noLPB',
		  'lpb_items.qty AS qty',
		  'po_items.price as price',
		  'po_items.price_discount as price_discount',
		  'po_items.discount AS discount',
		  'master_item_products.name AS product',
		  'master_item_products.id AS productID',
		  'departments.name AS department',
		  'po_items.specification',
		  'suppliers.id AS supplierID',
		  'master_item_products.code AS productCode',
		  'master_item_products.part_number AS productPartNumber',
		  'master_item_brands.name AS productBrand',
		  'po_items.measure',
		  'currencies.name AS symbol',
		  'spb.type',
		  'po.ppn AS ppn',
		  'po.discount_type AS po_discount_type'

	   )
	  ->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
	  ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
	  ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
	  ->leftJoin('lpb', 'lpb_items.lpb_id', '=', 'lpb.id')
	  ->leftJoin('po', 'po_items.po_id', '=', 'po.id')
	  ->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'po.purchase_id')
	  ->leftJoin('master_item_products', 'master_item_products.id', '=', 'lpb_items.product_id')
	  ->leftJoin('master_item_brands', 'master_item_brands.id', '=', 'master_item_products.brand_id')
	   ->leftJoin('suppliers', 'suppliers.id', '=', 'po.supplier_id')
	  ->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
	  ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
	  ->leftJoin('currencies','currencies.name','=','po.currency')
	  ->where('spb_kolis.spb_id', $id)
	  ->where('spb_kolis.status_insurance', 0)
	  ->orderBy('no_koli', 'ASC')
	  ->get();
	return $query;

 }

 function getStatusInsurance($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Published</span>";
		} else {
			return "Published";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Insurance Issued</span>";
		} else {
			return "Insurance Issued";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Cancel</span>";
		} else {
			return "Cancel";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

// NEW
function getApprovalFirstPurchasing($po_id){
    $query = DB::table('po_histories')
	    ->select('users.email', 'users.name', 'users.notification_email', 'po_histories.user_id','users.ttd')
        ->leftJoin('users', 'users.id', '=', 'po_histories.user_id')
        ->where('po_histories.jenis','=','approval')
        ->where('po_histories.po_id','=',$po_id)
        ->orderBy('po_histories.created_at','ASC')
        ->first();
    return $query;
}

function getFirstApprovalDph($dph_id) {
    $query = DB::table('dph_histories')
        ->select(
            'users.email',
            'users.name',
            'users.notification_email',
            'dph_histories.user_id',
            'users.ttd',
            DB::raw('MAX(dph_histories.created_at) as last_approval_time')
        )
        ->leftJoin('users', 'users.id', '=', 'dph_histories.user_id')
        ->where('dph_histories.jenis', '=', 'approval')
        ->where('dph_histories.dph_id', '=', $dph_id)
        ->groupBy('dph_histories.user_id', 'users.email', 'users.name', 'users.notification_email', 'users.ttd')
        ->orderBy('last_approval_time', 'ASC')
        ->first();

    return $query;
}


function getTTDUserByID($id)
{
	$users = DB::table('users')
		->select('users.*')
		->where('id', $id)
		->first();
	return $users;
}
function getStatusDPH($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress</span>";
		} else {
			return "On Progress";
		}
	}else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>On Progress Approval</span>";
		} else {
			return "On Progress Approval";
		}
	} else if ($status == 3) {
		if ($raw == null) {
			return "<span class='badge badge-purple'>Revisi</span>";
		} else {
			return "Revisi";
		}
	}
	 else if ($status == 4) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done </span>";
		} else {
			return "Done";
		}
	} else if ($status == 5) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Cancel</span>";
		} else {
			return "Cancel";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}
function getStatusDphSupplier($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Approved</span>";
		} else {
			return "Approved";
		}
	} else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-danger'>Cancel </span>";
		} else {
			return "Cancel";
		}
	}else {
		if ($raw == null) {
			return "<span class='badge badge-info'>On Progress</span>";
		} else {
			return "On Progress";
		}
	}
}

function getDphByPr($id){
	$query = DB::table('dph')
		->select(
			'dph.*',
			'purchases.mr_file',
			'purchases.type AS type_dpm',
			'purchase_requisitions.doc_no AS pr_no',
			'purchase_requisitions.purchase_id AS dpm_id',
			'purchase_requisitions.dpm_no AS dpm_no',
			'purchase_requisitions.location_id AS location_id',
			'locations.name AS location',
			'companies.name AS company',
			'companies.alias AS company_code',
			'companies.id AS company_id',
			'companies.address AS companyAddress',
			'companies.telp AS companyTelp',
			'companies.fax AS companyFax',
			'created_users.name AS created',
			'departments.name AS department',
			'projects.name AS project',
			'approval.name AS position'
		)
		->leftJoin('purchase_requisitions', 'purchase_requisitions.id', '=', 'dph.purchase_id')
		->leftJoin('purchases', 'purchase_requisitions.purchase_id', '=', 'purchases.id')
		->leftJoin('users AS created_users', 'created_users.id', '=', 'dph.created_by')
		->leftJoin('users AS approval', 'approval.id', '=', 'dph.position')
		->leftJoin('departments', 'departments.id', '=', 'purchase_requisitions.department_id')
		->leftJoin('locations', 'locations.id', '=', 'purchase_requisitions.location_id')
		->leftJoin('companies', 'companies.id', '=', 'locations.company_id')
		->leftJoin('projects', 'projects.id', '=', 'purchases.project_id')
		->leftJoin('dph_suppliers','dph_suppliers.dph_id','=','dph.id')
		->where('purchase_requisitions.id', $id)
		->get();
	return $query;

}

function getSupplierByDph($id){
    $query = DB::table('dph_suppliers')
        ->select(
        	'dph_suppliers.*',
          	'suppliers.name AS supplier',
          	'suppliers.address AS alamat_supplier',
			'supplier_contacts.name AS picName',
			'supplier_contacts.telp AS picTelp',
			'supplier_contacts.email AS picEmail',
			'supplier_contacts.title AS picTitle',
			'currencies.name AS currencysymbol',
			'currencies.name AS currency',
			'payment_terms.name AS payment_term'
        )
		->leftJoin('suppliers', 'suppliers.id', '=', 'dph_suppliers.supplier_id')
		->leftJoin('currencies', 'currencies.name', '=', 'dph_suppliers.currency')
		->leftJoin('supplier_contacts', 'dph_suppliers.supplier_contact_id', '=', 'supplier_contacts.id')
		->leftJoin('payment_terms', 'payment_terms.id', '=', 'dph_suppliers.payment_term_id')
		->orderBy('dph_suppliers.id','ASC')
        ->where('dph_suppliers.dph_id', $id)
        ->get();
    return $query;
}

function getDphItemByDphSupplier($id){
    $query = DB::table('dph_items')
        ->select(
            'dph_items.*',
            'purchase_items.qty_parsial AS qty_parsial_po',
            'purchase_items.qty AS qty_pr',
            'purchase_items.flag',
            'purchase_items.needed_on_date',
            'purchase_items.po_status',
            'master_item_products.name AS product',
            'master_item_products.code AS product_code',
            'master_item_products.part_number AS productPartNumber',
            'master_item_brands.name AS productBrand'
        )
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'dph_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'dph_items.pr_item_id')
		->leftJoin('dph_suppliers','dph_suppliers.id','=','dph_items.dph_supplier_id')
		->orderBy('dph_items.id','ASC')
        ->where('dph_items.dph_supplier_id', $id)
        ->get();
    return $query;
}
function getCheckDphItem($dph_id,$dph_supplier_id,$pr_item_id){
    $query = DB::table('dph_items')
            ->select('dph_items.*')
            ->leftJoin('dph_suppliers', 'dph_suppliers.id', '=', 'dph_items.dph_supplier_id') // Corrected column name here
            ->where('dph_suppliers.dph_id', $dph_id)
            ->where('dph_suppliers.id','!=', $dph_supplier_id)
            ->where('dph_items.pr_item_id', $pr_item_id)
            ->where('dph_items.is_recomendation',1)
            ->first();
    return $query;
}
function getCheckDphItemAdd($dph_id,$pr_item_id){
    $query = DB::table('dph_items')
            ->select('dph_items.*')
            ->leftJoin('dph_suppliers', 'dph_suppliers.id', '=', 'dph_items.dph_supplier_id') // Corrected column name here
            ->where('dph_suppliers.dph_id', $dph_id)
            ->where('dph_items.pr_item_id', $pr_item_id)
            ->where('dph_items.is_recomendation',1)
            ->first();
    return $query;
}
function getHistoryDPH($id_dph){
    $query = DB::table('dph_histories')
        ->select('dph_histories.*')
        ->where('dph_histories.dph_id','=',$id_dph)
        ->orderBy('dph_histories.created_at','DESC')
        ->get();
    return $query;
}
function getStatusItemLpbSunkel($in, $out, $raw = null)
{
	if ($in > $out) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>On Progress</span>";
		} else {
			return "On Progress";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	}
}
function getStatusMonitoringItemLpb($spb_status,$raw = null)
{
	if ($spb_status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-primary'>OUT</span>";
		} else {
			return "OUT";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-danger'>SOH</span>";
		} else {
			return "SOH";
		}
	}
}

function sendWhatsapp($telp,$body){
	$curl = curl_init();
	$token = "QCAJBftXQTi2ZbJMkhp4";
	curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://api.fonnte.com/send',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => array(
		'target' => $telp,
		'message' => $body,
		),
	CURLOPT_HTTPHEADER => array(
			"Authorization: $token"
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	echo $response;
}
function sendWhatsappSupPo($telp,$body){
	$curl = curl_init();
	$token = "SXn2rxqejzVZPrzEmZ9c";
	curl_setopt_array($curl, array(
	CURLOPT_URL => 'https://api.fonnte.com/send',
	CURLOPT_RETURNTRANSFER => true,
	CURLOPT_ENCODING => '',
	CURLOPT_MAXREDIRS => 10,
	CURLOPT_TIMEOUT => 0,
	CURLOPT_FOLLOWLOCATION => true,
	CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
	CURLOPT_CUSTOMREQUEST => 'POST',
	CURLOPT_POSTFIELDS => array(
		'target' => $telp,
		'message' => $body,
        'delay' => '5-10',
		),
	CURLOPT_HTTPHEADER => array(
			"Authorization: $token"
		),
	));
	$response = curl_exec($curl);
	curl_close($curl);
	echo $response;
}

function getStatusEmailPO($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null)  return "<span class='badge badge-primary'>Aktif</span>";
		else return "Aktif";
	}else {
		if ($raw == null) return "<span class='badge badge-danger'>Tidak Aktif</span>";
		else return "Tidak Aktif";
	}
}
function getTypeBodyEmal($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null)  return "<span class='badge badge-primary'>CDB</span>";
		else return "CBD";
    }
	else if ($status == 2) {
		if ($raw == null)  return "<span class='badge badge-secondary'>COD</span>";
		else return "COD";
	}
    else if ($status == 3) {
		if ($raw == null)  return "<span class='badge badge-success'>DP</span>";
		else return "DP";
	}
    else if ($status == 4) {
		if ($raw == null)  return "<span class='badge badge-warning'>TEMPO</span>";
		else return "TEMPO";
	}else {
		if ($raw == null) return "<span class='badge badge-danger'>Default</span>";
		else return "Default";
	}
}

function isAdminEmail()
{
	if (auth()->user()->can('admin_email')) {
		return true;
	} else {
		return false;
	}
}
function isAdminPurchasing()
{
	if (Auth::user()->type == 4 && Auth::user()->data_access == 1) {
		return true;
	} else {
		return false;
	}
}
function getSupplierById($id)
{
	$suppliers = DB::table('suppliers')
		->select('suppliers.*')
		->where('id', $id)
		->first();
	if ($suppliers) {
		return $suppliers->name;
	} else {
		return "-";
	}
}

function getPaymentIdByName($name)
{
    if($name){
        $payment_methods = DB::table('payment_methods')
            ->select('payment_methods.*')
            ->where('name', '=' , $name)
            ->first();
        if ($payment_methods) {
            return $payment_methods->id;
        } else {
            return null;
        }
    }else{
        return null;
    }

}

function getStatusAllItem($typeDpm = null , $statusDpm = null, $statusPurchaseItem = null, $pr_statusPurchaseItem = null, $po_statusPurchaseItem = null, $statusPo = null, $lpb_statusPoItem = null, $statusLpb = null, $spb_statusLpb = null, $statusLpbItem = null, $statusSpb = null, $bpb_statusSpbKolis = null, $qty_parsialPurchaseItem = null, $typePo = null, $raw = null)
{
	// PETTY CASH and IM
	if ($typeDpm == 'petty_cash' || $typeDpm == 'im'){
		if ($statusPurchaseItem == 1) {
			if ($statusDpm == 3 ) {
				if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
				else return "Hold";
			}
			else {
				if ($raw == null) return "<span class='badge badge-info'>On Progress Approval</span>";
				else return "On Progress Approval";
			}
		} else if ($statusPurchaseItem == 2) {
			if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
			else return "Rejected";
		} else if ($statusPurchaseItem == 3) {
			if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
			else return "Cancel";
		} else if ($statusPurchaseItem == 4) {
			if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 0) {
				if ($raw == null) return "<span class='badge badge-primary'>PR Issued</span>";
				else return "PR Issued";
			}
			if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 1) {
				if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDpm)." Issued</span>";
				else return strtoupper($typeDpm)." Issued";
			}
			if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 2) {
				if ($raw == null)return "<span class='badge badge-purple'>PR Parsial</span>";
				else return "PR Parsial";
			}
			if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 3) {
				if ($qty_parsialPurchaseItem == 0) {
					if ($raw == null) return "<span class='badge badge-primary'>PR Closed</span>";
					else return "PR Closed";
				} else {
					if ($raw == null) return "<span class='badge badge-success'>". strtoupper($typeDpm). " Issued</span>";
					else return strtoupper($typeDpm)." Issued";
				}
			}
		} else if ($statusPurchaseItem == 5) {
			if ($raw == null) return "<span class='badge badge-success'>". strtoupper($typeDpm). " Issued</span>";
			else return strtoupper($typeDpm)." Issued";
		} else {
			if ($raw == null)  return "<span class='badge badge-warning'>Draft</span>";
			else return "Draft";
		}
	}
	// PO
	else {
		if ($statusPurchaseItem == 1) {
			if ($statusDpm == 3 ) {
				if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
				else return "Hold";
			}
			else {
				if ($raw == null) return "<span class='badge badge-info'>On Progress Approval</span>";
				else return "On Progress Approval";
			}
		} else if ($statusPurchaseItem == 2) {
			if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
			else return "Rejected";
		} else if ($statusPurchaseItem == 3) {
			if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
			else return "Cancel";
		} else if ($statusPurchaseItem == 4) {
			if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 0) {
				if ($raw == null) return "<span class='badge badge-primary'>PR Issued</span>";
				else return "PR Issued";
			}
			else if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 1) {
				// 1
				if($statusPo == 1){
					if ($raw == null) return "<span class='badge badge-primary'>On Progress Aprroval PO</span>";
					else return "On Progress Aprroval PO";
				} else if($statusPo == 2){
					if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
					else return "PO Issued";
				} else if($statusPo == 3){
					if ($raw == null) return "<span class='badge badge-purple'>Perbaikan PO</span>";
					else return "Perbaikan PO";
				} else if($statusPo == 4){
					// 1.5
					// LPB
					if($typePo == 'lpb'){
						// 2
						if($lpb_statusPoItem == 1){
							// 3
							if($statusLpb == 1){
								// 4
								if($spb_statusLpb == 1){
									// 5
									if($statusLpbItem == 1){
										// 6
										if($statusSpb == 1){
											if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
											else return "SPB Issued";
										} else if($statusSpb == 2){
											if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
											else return "SPB Parsial";
										} else if($statusSpb == 3){
											// 7
											if($bpb_statusSpbKolis == 1){
												if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
												else return "BPB Done";
											} else if($bpb_statusSpbKolis == 2){
												if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
												else return "SPB Parsial";
											} else {
												if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
												else return "SPB Issued";
											}
										} else if($statusSpb == 4){
											if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
											else return "LPB Issued";
										} else if($statusSpb == 5){
											if ($raw == null) return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										} else {
											if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
											else return "LPB Issued";
										}
									}else if($statusLpbItem == 2){
										if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
										else return "Parsial";
									}else {
										if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
										else return "LPB Issued";
									}
								} else if($spb_statusLpb == 2){
									if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
									else return "Parsial";
								} else {
									if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
									else return "LPB Issued";
								}
							} else { // status = 4 and 0 (table lpb)
								if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
								else return "PO Issued";
							}
						} else if($lpb_statusPoItem == 2){
							if ($raw == null) return "<span class='badge badge-primary'>PO Parsial</span>";
							else return "PO Parsial";
						} else {
							if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
							else return "PO Issued";
						}
					}
					// Non LPB
					else {
						if($lpb_statusPoItem == 1) {
							if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
							else return "BPB Done";
						} else if($lpb_statusPoItem == 2) {
							if ($raw == null) return "<span class='badge badge-primary'>BPB Parsial</span>";
							else return "BPB Parsial";
						} else {
							if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
							else return "PO Issued";
						}
					}
				} else if($statusPo == 5){
					// 1.5
					// LPB
					if($typePo == 'lpb'){
						// 2
						if($lpb_statusPoItem == 1){
							// 3
							if($statusLpb == 1){
								// 4
								if($spb_statusLpb == 1){
									// 5
									if($statusLpbItem == 1){
										// 6
										if($statusSpb == 1){
											if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
											else return "SPB Issued";
										} else if($statusSpb == 2){
											if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
											else return "SPB Parsial";
										} else if($statusSpb == 3){
											// 7
											if($bpb_statusSpbKolis == 1){
												if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
												else return "BPB Done";
											} else if($bpb_statusSpbKolis == 2){
												if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
												else return "SPB Parsial";
											} else {
												if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
												else return "SPB Issued";
											}
										} else if($statusSpb == 4){
											if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
											else return "LPB Issued";
										} else if($statusSpb == 5){
											if ($raw == null) return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										} else {
											if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
											else return "LPB Issued";
										}
									}else if($statusLpbItem == 2){
										if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
										else return "Parsial";
									}else {
										if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
										else return "LPB Issued";
									}
								} else if($spb_statusLpb == 2){
									if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
									else return "Parsial";
								} else {
									if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
									else return "LPB Issued";
								}
							} else { // status = 4 and 0 (table lpb)
								if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
								else return "PO Issued";
							}
						} else if($lpb_statusPoItem == 2){
							if ($raw == null) return "<span class='badge badge-primary'>PO Parsial</span>";
							else return "PO Parsial";
						} else {
							if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
							else return "PO Issued";
						}
					}
					// Non LPB
					else {
						if($lpb_statusPoItem == 1) {
							if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
							else return "BPB Done";
						} else if($lpb_statusPoItem == 2) {
							if ($raw == null) return "<span class='badge badge-primary'>BPB Parsial</span>";
							else return "BPB Parsial";
						} else {
							if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
							else return "PO Issued";
						}
					}
				} else if($statusPo == 6){
					if ($raw == null) return "<span class='badge badge-danger'>Cancel PO</span>";
					else return "Cancel PO";
				} else if($statusPo == 8){
					if ($raw == null) return "<span class='badge badge-danger'>Revised PO (Closed)</span>";
					else return "Revised PO (Closed)";
				} else if($statusPo == 9){
					if ($raw == null) return "<span class='badge badge-warning'>Revision Draft PO</span>";
					else return "Revision Draft PO";
				} else if($statusPo == 10){
					if ($raw == null) return "<span class='badge badge-warning'>Draft PO</span>";
					else return "Draft PO";
				} else {
					if ($raw == null) return "<span class='badge badge-warning'>Draft PO</span>";
					else return "Draft PO";
				}
			}
			else if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 2) {
				if ($raw == null)return "<span class='badge badge-primary'>PR Parsial</span>";
				else return "PR Parsial";
			}
			else if ($pr_statusPurchaseItem == 1 && $po_statusPurchaseItem == 3) {
				if ($qty_parsialPurchaseItem == 0) {
					if ($raw == null) return "<span class='badge badge-danger'>PR Closed</span>";
					else return "PR Closed";
				}
				else {
					if ($raw == null) return "<span class='badge badge-primary'>PR Parsial</span>";
					else return "PR Parsial";
				}
			}
		} else if ($statusPurchaseItem == 5) {
			if ($raw == null) return "<span class='badge badge-success'>". strtoupper($typeDpm). " Issued</span>";
			else return strtoupper($typeDpm)." Issued";
		} else {
			if ($raw == null)  return "<span class='badge badge-warning'>Draft</span>";
			else return "Draft";
		}
	}
}

function getStatusMonitoringItemPo($statusPo = null, $lpb_statusPoItem = null, $statusLpb = null, $spb_statusLpb = null, $statusLpbItem = null, $statusSpb = null, $bpb_statusSpbKolis = null, $typePo = null, $raw = null)
{
	// 1
	if($statusPo == 1){
		if ($raw == null) return "<span class='badge badge-primary'>On Progress Aprroval PO</span>";
		else return "On Progress Aprroval PO";
	} else if($statusPo == 2){
		if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
		else return "PO Issued";
	} else if($statusPo == 3){
		if ($raw == null) return "<span class='badge badge-purple'>Perbaikan PO</span>";
		else return "Perbaikan PO";
	} else if($statusPo == 4){
		// 1.5
		// LPB
		if($typePo == 'lpb'){
			// 2
			if($lpb_statusPoItem == 1){
				// 3
				if($statusLpb == 1){
					// 4
					if($spb_statusLpb == 1){
						// 5
						if($statusLpbItem == 1){
							// 6
							if($statusSpb == 1){
								if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
								else return "SPB Issued";
							} else if($statusSpb == 2){
								if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
								else return "SPB Parsial";
							} else if($statusSpb == 3){
								// 7
								if($bpb_statusSpbKolis == 1){
									if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
									else return "BPB Done";
								} else if($bpb_statusSpbKolis == 2){
									if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
									else return "SPB Parsial";
								} else {
									if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
									else return "SPB Issued";
								}
							} else if($statusSpb == 4){
								if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
								else return "LPB Issued";
							} else if($statusSpb == 5){
								if ($raw == null) return "<span class='badge badge-info'>On Progress SPB</span>";
								else return "On Progress SPB";
							} else {
								if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
								else return "LPB Issued";
							}
						}else if($statusLpbItem == 2){
							if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
							else return "Parsial";
						}else {
							if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
							else return "LPB Issued";
						}
					} else if($spb_statusLpb == 2){
						if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
						else return "Parsial";
					} else {
						if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
						else return "LPB Issued";
					}
				} else { // status = 4 and 0 (table lpb)
					if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
					else return "PO Issued";
				}
			} else if($lpb_statusPoItem == 2){
				if ($raw == null) return "<span class='badge badge-primary'>PO Parsial</span>";
				else return "PO Parsial";
			} else {
				if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
				else return "PO Issued";
			}
		}
		// Non LPB
		else {
			if($lpb_statusPoItem == 1) {
				if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
				else return "BPB Done";
			} else if($lpb_statusPoItem == 2) {
				if ($raw == null) return "<span class='badge badge-primary'>BPB Parsial</span>";
				else return "BPB Parsial";
			} else {
				if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
				else return "PO Issued";
			}
		}
	} else if($statusPo == 5){
		// 1.5
		// LPB
		if($typePo == 'lpb'){
			// 2
			if($lpb_statusPoItem == 1){
				// 3
				if($statusLpb == 1){
					// 4
					if($spb_statusLpb == 1){
						// 5
						if($statusLpbItem == 1){
							// 6
							if($statusSpb == 1){
								if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
								else return "SPB Issued";
							} else if($statusSpb == 2){
								if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
								else return "SPB Parsial";
							} else if($statusSpb == 3){
								// 7
								if($bpb_statusSpbKolis == 1){
									if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
									else return "BPB Done";
								} else if($bpb_statusSpbKolis == 2){
									if ($raw == null) return "<span class='badge badge-primary'>SPB Parsial</span>";
									else return "SPB Parsial";
								} else {
									if ($raw == null) return "<span class='badge badge-primary'>SPB Issued</span>";
									else return "SPB Issued";
								}
							} else if($statusSpb == 4){
								if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
								else return "LPB Issued";
							} else if($statusSpb == 5){
								if ($raw == null) return "<span class='badge badge-info'>On Progress SPB</span>";
								else return "On Progress SPB";
							} else {
								if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
								else return "LPB Issued";
							}
						}else if($statusLpbItem == 2){
							if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
							else return "Parsial";
						}else {
							if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
							else return "LPB Issued";
						}
					} else if($spb_statusLpb == 2){
						if ($raw == null) return "<span class='badge badge-primary'>Parsial</span>";
						else return "Parsial";
					} else {
						if ($raw == null) return "<span class='badge badge-primary'>LPB Issued</span>";
						else return "LPB Issued";
					}
				} else { // status = 4 and 0 (table lpb)
					if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
					else return "PO Issued";
				}
			} else if($lpb_statusPoItem == 2){
				if ($raw == null) return "<span class='badge badge-primary'>PO Parsial</span>";
				else return "PO Parsial";
			} else {
				if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
				else return "PO Issued";
			}
		}
		// Non LPB
		else {
			if($lpb_statusPoItem == 1) {
				if ($raw == null) return "<span class='badge badge-success'>BPB Done</span>";
				else return "BPB Done";
			} else if($lpb_statusPoItem == 2) {
				if ($raw == null) return "<span class='badge badge-primary'>BPB Parsial</span>";
				else return "BPB Parsial";
			} else {
				if ($raw == null) return "<span class='badge badge-primary'>PO Issued</span>";
				else return "PO Issued";
			}
		}
	} else if($statusPo == 6){
		if ($raw == null) return "<span class='badge badge-danger'>Cancel PO</span>";
		else return "Cancel PO";
	} else if($statusPo == 8){
		if ($raw == null) return "<span class='badge badge-danger'>Revised PO (Closed)</span>";
		else return "Revised PO (Closed)";
	} else if($statusPo == 9){
		if ($raw == null) return "<span class='badge badge-warning'>Revision Draft PO</span>";
		else return "Revision Draft PO";
	} else if($statusPo == 10){
		if ($raw == null) return "<span class='badge badge-warning'>Draft PO</span>";
		else return "Draft PO";
	} else {
		if ($raw == null) return "<span class='badge badge-warning'>Draft PO</span>";
		else return "Draft PO";
	}
}

function getStatusLPBItem($status, $raw = null){
	if ($status == 0) {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Belum SPB</span>";
		} else {
			return "Belum SPB";
		}
	}
	else if ($status == 1) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	}
	else if ($status == 2) {
		if ($raw == null) {
			return "<span class='badge badge-info'>Parsial</span>";
		} else {
			return "Parsial";
		}
	}
}

function getQtyAllPoItemByPurchaseItem($id_purchase_items){
	$query = DB::table('po_items')
	->select('po_items.qty')
	->leftJoin('po', 'po.id', '=', 'po_items.po_id')
	->where('po_items.pr_item_id', '=', $id_purchase_items)
	->whereIn('po.status', [1,2,3,4,5,9,10])
	->sum('po_items.qty');

	return $query;
}

function getQtyAllLpbItemByPurchaseItem($id_purchase_items){
	$query = DB::table('lpb_items')
	->select('lpb_items.qty')
	->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
	->where('lpb_items.pr_item_id', '=', $id_purchase_items)
	->whereIn('lpb.status', [1,2])
	->sum('lpb_items.qty');

	return $query;
}

function getQtyAllSpbItemByPurchaseItem($id_purchase_items){
	$query = DB::table('spb_kolis')
	->select('spb_kolis.qty')
	->leftJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
	->where('spb_kolis.pr_item_id', '=', $id_purchase_items)
	->whereIn('spb.status', [1,2,3])
	->sum('spb_kolis.qty');

	return $query;
}

function getQtyAllBpbItemByPurchaseItem($id_purchase_items){
	$query = DB::table('bpb_items')
	->select('bpb_items.qty')
	->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
	->where('bpb_items.pr_item_id', '=', $id_purchase_items)
	->whereIn('bpb.status', [1,2])
	->sum('bpb_items.qty');

	return $query;
}

function getTypePoByPurchaseItem($id_purchase_items){
	$query = DB::table('po_items')
	->leftJoin('po', 'po.id', '=', 'po_items.po_id')
	->where('po_items.pr_item_id', '=', $id_purchase_items)
	->whereIn('po.status', [1,2,3,4,5,9,10])
	->select('po.type')
	->orderBy('po.id', 'DESC')
	->first();

	return $query;
}

function getStatusItemByQty($typeDpm = null, $statusPrItem = null, $statusDpm = null,  $pr_statusPrItem = null, $po_statusPrItem = null, $statusPr = null, $typePo = null, $qtyPo = null, $qtyLpb = null, $qtySpb = null, $qtyBpb = null, $qtyPrItem = null, $qty_parsialPurchaseItems = null, $raw = null)
{
	if ($typeDpm == 'petty_cash' || $typeDpm == 'im'){
		if ($statusPrItem == 1) {
			if ($statusDpm == 3 ) {
				if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
				else return "Hold";
			}
			else if($statusDpm == 11) {
				if ($raw == null)return "<span class='badge badge-info'>On Progress DPM</span>";
				else return "On Progress DPM";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>On Progress Approval DPM</span>";
				else return "On Progress Approval DPM";
			}
		} else if ($statusPrItem == 2) {
			if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
			else return "Rejected";
		} else if ($statusPrItem == 3) {
			if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
			else return "Cancel";
		} else if ($statusPrItem == 4) {
			if ($pr_statusPrItem == 1) {
				if ($statusPr == 1) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDpm)." Issued</span>";
						else return strtoupper($typeDpm)." Issued";
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial ".strtoupper($typeDpm)."</span>";
						else return "Parsial ".strtoupper($typeDpm);
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
						else return "Closed, Parsial ".strtoupper($typeDpm);
					}
				} else if ($statusPr == 2) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDpm)." Issued</span>";
						else return strtoupper($typeDpm)." Issued";
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial ".strtoupper($typeDpm)."</span>";
						else return "Parsial ".strtoupper($typeDpm);
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
						else return "Closed, Parsial ".strtoupper($typeDpm);
					}
				} else if ($statusPr == 3) {
					if ($raw == null) {
						return "<span class='badge badge-purple'>Revisi PR</span>";
					} else {
						return "Revisi PR";
					}
				} else if ($statusPr == 4) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDpm)." Issued</span>";
						else return strtoupper($typeDpm)." Issued";
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial ".strtoupper($typeDpm)."</span>";
						else return "Parsial ".strtoupper($typeDpm);
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
						else return "Closed, Parsial ".strtoupper($typeDpm);
					}
				} else if ($statusPr == 5) {
					if ($raw == null) {
						return "<span class='badge badge-danger'>Closed PR</span>";
					} else {
						return "Closed PR";
					}
				} else if ($statusPr == 6) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if ($raw == null) return "<span class='badge badge-success'>".strtoupper($typeDpm)." Issued</span>";
						else return strtoupper($typeDpm)." Issued";
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial ".strtoupper($typeDpm)."</span>";
						else return "Parsial ".strtoupper($typeDpm);
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
						else return "Closed, Parsial ".strtoupper($typeDpm);
					}
				} else {
					if ($raw == null) {
						return "<span class='badge badge-info'>On Progress PR</span>";
					} else {
						return "On Progress PR";
					}
				}
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>On Progress Approval DPM</span>";
				else return "On Progress Approval DPM";
			}
		} else {
			if ($raw == null)  return "<span class='badge badge-warning'>Draft DPM</span>";
			 else return "Draft DPM";
		}
	}

	// PO
	else {
		if ($statusPrItem == 1) {
			if ($statusDpm == 3 ) {
				if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
				else return "Hold";
			}
			else if($statusDpm == 11) {
				if ($raw == null)return "<span class='badge badge-info'>On Progress DPM</span>";
				else return "On Progress DPM";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>On Progress Approval DPM</span>";
				else return "On Progress Approval DPM";
			}
		} else if ($statusPrItem == 2) {
			if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
			else return "Rejected";
		} else if ($statusPrItem == 3) {
			if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
			else return "Cancel";
		} else if ($statusPrItem == 4) {
			if ($pr_statusPrItem == 1) {
				if ($statusPr == 1) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if($qtyPrItem <= $qtyPo){
							if($typePo == 'lpb'){ // LPB
								if($qtyPrItem <= $qtyLpb){
									if($qtyPrItem <= $qtySpb){
										if($qtyPrItem <= $qtyBpb){
											if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
											else return "Done BPB";
										}
										else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
											if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
											else return "Parsial BPB";
										}
										else {
											if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										}
									}
									else if ($qtyPrItem > $qtySpb && $qtySpb > 0){
										if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
										else return "Parsial SPB";
									}
									else {
										if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
										else return "On Progress LPB";
									}
								}
								else if ($qtyPrItem > $qtyLpb && $qtyLpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
									else return "Parsial LPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
							else { // Non LPB
								if($qtyPrItem <= $qtyBpb){
									if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
									else return "Done BPB";
								}
								else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
									else return "Parsial BPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
						}
						else if ($qtyPrItem > $qtyPo && $qtyPo > 0){
							if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
							else return "Parsial PO";
						}
						else {
							if ($raw == null)return "<span class='badge badge-info'>On Progress PR</span>";
							else return "On Progress PR";
						}
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
						else return "Parsial PO";
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
						else return "Closed, Parsial PO";
					}
				} else if ($statusPr == 2) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if($qtyPrItem <= $qtyPo){
							if($typePo == 'lpb'){ // LPB
								if($qtyPrItem <= $qtyLpb){
									if($qtyPrItem <= $qtySpb){
										if($qtyPrItem <= $qtyBpb){
											if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
											else return "Done BPB";
										}
										else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
											if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
											else return "Parsial BPB";
										}
										else {
											if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										}
									}
									else if ($qtyPrItem > $qtySpb && $qtySpb > 0){
										if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
										else return "Parsial SPB";
									}
									else {
										if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
										else return "On Progress LPB";
									}
								}
								else if ($qtyPrItem > $qtyLpb && $qtyLpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
									else return "Parsial LPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
							else { // Non LPB
								if($qtyPrItem <= $qtyBpb){
									if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
									else return "Done BPB";
								}
								else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
									else return "Parsial BPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
						}
						else if ($qtyPrItem > $qtyPo && $qtyPo > 0){
							if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
							else return "Parsial PO";
						}
						else {
							if ($raw == null)return "<span class='badge badge-info'>On Progress PR</span>";
							else return "On Progress PR";
						}
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
						else return "Parsial PO";
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial ".strtoupper($typeDpm)."</span>";
							else return "Closed, Parsial ".strtoupper($typeDpm);
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
						else return "Closed, Parsial PO";
					}
				} else if ($statusPr == 3) {
					if ($raw == null) {
						return "<span class='badge badge-purple'>Revisi PR</span>";
					} else {
						return "Revisi PR";
					}
				} else if ($statusPr == 4) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if($qtyPrItem <= $qtyPo){
							if($typePo == 'lpb'){ // LPB
								if($qtyPrItem <= $qtyLpb){
									if($qtyPrItem <= $qtySpb){
										if($qtyPrItem <= $qtyBpb){
											if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
											else return "Done BPB";
										}
										else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
											if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
											else return "Parsial BPB";
										}
										else {
											if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										}
									}
									else if ($qtyPrItem > $qtySpb && $qtySpb > 0){
										if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
										else return "Parsial SPB";
									}
									else {
										if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
										else return "On Progress LPB";
									}
								}
								else if ($qtyPrItem > $qtyLpb && $qtyLpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
									else return "Parsial LPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
							else { // Non LPB
								if($qtyPrItem <= $qtyBpb){
									if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
									else return "Done BPB";
								}
								else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
									else return "Parsial BPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
						}
						else if ($qtyPrItem > $qtyPo && $qtyPo > 0){
							if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
							else return "Parsial PO";
						}
						else {
							if ($raw == null)return "<span class='badge badge-info'>On Progress PR</span>";
							else return "On Progress PR";
						}
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
						else return "Parsial PO";
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
							else return "Closed, Parsial PO";
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
						else return "Closed, Parsial PO";
					}
				} else if ($statusPr == 5) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if($qtyPrItem <= $qtyPo){
							if($typePo == 'lpb'){ // LPB
								if($qtyPrItem <= $qtyLpb){
									if($qtyPrItem <= $qtySpb){
										if($qtyPrItem <= $qtyBpb){
											if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
											else return "Done BPB";
										}
										else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
											if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
											else return "Parsial BPB";
										}
										else {
											if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										}
									}
									else if ($qtyPrItem > $qtySpb && $qtySpb > 0){
										if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
										else return "Parsial SPB";
									}
									else {
										if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
										else return "On Progress LPB";
									}
								}
								else if ($qtyPrItem > $qtyLpb && $qtyLpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
									else return "Parsial LPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
							else { // Non LPB
								if($qtyPrItem <= $qtyBpb){
									if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
									else return "Done BPB";
								}
								else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
									else return "Parsial BPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
						}
						else if ($qtyPrItem > $qtyPo && $qtyPo > 0){
							if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
							else return "Parsial PO";
						}
						else {
							if ($raw == null)return "<span class='badge badge-info'>On Progress PR</span>";
							else return "On Progress PR";
						}
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
						else return "Parsial PO";
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
							else return "Closed, Parsial PO";
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
						else return "Closed, Parsial PO";
					}
				} else if ($statusPr == 6) {
					if ($po_statusPrItem == 0) {
						if ($raw == null) return "<span class='badge badge-info'>On Progress PR</span>";
						else return "On Progress PR";
					}
					else if ($po_statusPrItem == 1) {
						if($qtyPrItem <= $qtyPo){
							if($typePo == 'lpb'){ // LPB
								if($qtyPrItem <= $qtyLpb){
									if($qtyPrItem <= $qtySpb){
										if($qtyPrItem <= $qtyBpb){
											if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
											else return "Done BPB";
										}
										else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
											if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
											else return "Parsial BPB";
										}
										else {
											if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
											else return "On Progress SPB";
										}
									}
									else if ($qtyPrItem > $qtySpb && $qtySpb > 0){
										if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
										else return "Parsial SPB";
									}
									else {
										if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
										else return "On Progress LPB";
									}
								}
								else if ($qtyPrItem > $qtyLpb && $qtyLpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
									else return "Parsial LPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
							else { // Non LPB
								if($qtyPrItem <= $qtyBpb){
									if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
									else return "Done BPB";
								}
								else if ($qtyPrItem > $qtyBpb && $qtyBpb > 0){
									if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
									else return "Parsial BPB";
								}
								else {
									if ($raw == null)return "<span class='badge badge-info'>On Progress PO</span>";
									else return "On Progress PO";
								}
							}
						}
						else if ($qtyPrItem > $qtyPo && $qtyPo > 0){
							if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
							else return "Parsial PO";
						}
						else {
							if ($raw == null)return "<span class='badge badge-info'>On Progress PR</span>";
							else return "On Progress PR";
						}
					}
					else if ($po_statusPrItem == 2) {
						if ($raw == null)return "<span class='badge badge-primary'>Parsial PO</span>";
						else return "Parsial PO";
					}
					else if ($po_statusPrItem == 3) {
						if($qty_parsialPurchaseItems <= 0 || $qty_parsialPurchaseItems == null){
							if ($raw == null) return "<span class='badge badge-danger'>Closed PR</span>";
							else return "Closed PR";
						}else{
							if ($raw == null) return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
							else return "Closed, Parsial PO";
						}
					}
					else if ($po_statusPrItem == 4) {
						if ($raw == null)return "<span class='badge badge-danger'>Closed, Parsial PO</span>";
						else return "Closed, Parsial PO";
					}
				} else {
					if ($raw == null) {
						return "<span class='badge badge-info'>On Progress PR</span>";
					} else {
						return "On Progress PR";
					}
				}
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>On Progress Approval DPM</span>";
				else return "On Progress Approval DPM";
			}
		} else {
			if ($raw == null)  return "<span class='badge badge-warning'>Draft DPM</span>";
			 else return "Draft DPM";
		}
	}
}

function getQtyLpbByPoItemId($po_itemsId){
	$query = DB::table('lpb_items')
	->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
	->where('lpb_items.po_item_id', $po_itemsId)
	->whereIn('lpb.status', [1,2])
	->sum('lpb_items.qty');

	return $query;
}

function getQtySpbByPoItemId($po_itemsId){
	$query = DB::table('spb_kolis')
	->leftJoin('spb', 'spb.id', '=', 'spb_kolis.spb_id')
	->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
	->where('lpb_items.po_item_id', $po_itemsId)
	->whereIn('spb.status', [1,2,3])
	->sum('spb_kolis.qty');

	return $query;
}

function getQtyBpbJktByPoItemId($po_itemsId){
	$query = DB::table('bpb_items')
	->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
	->leftJoin('spb_kolis', 'spb_kolis.id', '=', 'bpb_items.spb_item_id')
	->leftJoin('lpb_items', 'lpb_items.id', '=', 'spb_kolis.spb_item_id')
	->where('lpb_items.po_item_id', $po_itemsId)
	->whereIn('bpb.status', [1,2])
	->sum('bpb_items.qty');

	return $query;
}

function getQtyBpbLklByPoItemId($po_itemsId){
	$query = DB::table('bpb_items')
	->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
	->leftJoin('po', 'po.id', '=', 'bpb.po_id')
	->leftJoin('po_items', 'po_items.po_id', '=', 'po.id')
	->where('po_items.id', $po_itemsId)
	->whereIn('bpb.status', [1,2])
	->sum('bpb_items.qty');

	return $query;
}

function getStatusPoItemByQty($typePo = null, $statusPo = null, $qtyPoItem = null, $qtyLpb = null, $qtySpb = null, $qtyBpbJkt = null, $qtyBpbLokal = null, $raw = null)
{
	if($typePo == 'lpb'){ // LPB
		if ($statusPo == 1) {
			if ($raw == null) {
				return "<span class='badge badge-info'>On Progress Approval PO</span>";
			} else {
				return "On Progress Approval PO";
			}
		} else if ($statusPo == 2) {
			if($qtyPoItem <= $qtyLpb){
				if($qtyPoItem <= $qtySpb){
					if($qtyPoItem <= $qtyBpbJkt){
						if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
						else return "Done BPB";
					}
					else if ($qtyPoItem > $qtyBpbJkt && $qtyBpbJkt > 0){
						if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
						else return "Parsial BPB";
					}
					else {
						if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
						else return "On Progress SPB";
					}
				}
				else if ($qtyPoItem > $qtySpb && $qtySpb > 0){
					if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
					else return "Parsial SPB";
				}
				else {
					if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
					else return "On Progress LPB";
				}
			}
			else if ($qtyPoItem > $qtyLpb && $qtyLpb > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
				else return "Parsial LPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>PO Issued</span>";
				else return "PO Issued";
			}
		} else if ($statusPo == 3) {
			if ($raw == null) {
				return "<span class='badge badge-purple'>Perbaikan</span>";
			} else {
				return "Perbaikan";
			}
		} else if ($statusPo == 4) {
			if($qtyPoItem <= $qtyLpb){
				if($qtyPoItem <= $qtySpb){
					if($qtyPoItem <= $qtyBpbJkt){
						if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
						else return "Done BPB";
					}
					else if ($qtyPoItem > $qtyBpbJkt && $qtyBpbJkt > 0){
						if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
						else return "Parsial BPB";
					}
					else {
						if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
						else return "On Progress SPB";
					}
				}
				else if ($qtyPoItem > $qtySpb && $qtySpb > 0){
					if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
					else return "Parsial SPB";
				}
				else {
					if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
					else return "On Progress LPB";
				}
			}
			else if ($qtyPoItem > $qtyLpb && $qtyLpb > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
				else return "Parsial LPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>Parsial LPB</span>";
				else return "Parsial LPB";
			}
		} else if ($statusPo == 5) {
			if($qtyPoItem <= $qtyLpb){
				if($qtyPoItem <= $qtySpb){
					if($qtyPoItem <= $qtyBpbJkt){
						if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
						else return "Done BPB";
					}
					else if ($qtyPoItem > $qtyBpbJkt && $qtyBpbJkt > 0){
						if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
						else return "Parsial BPB";
					}
					else {
						if ($raw == null)return "<span class='badge badge-info'>On Progress SPB</span>";
						else return "On Progress SPB";
					}
				}
				else if ($qtyPoItem > $qtySpb && $qtySpb > 0){
					if ($raw == null)return "<span class='badge badge-primary'>Parsial SPB</span>";
					else return "Parsial SPB";
				}
				else {
					if ($raw == null)return "<span class='badge badge-info'>On Progress LPB</span>";
					else return "On Progress LPB";
				}
			}
			else if ($qtyPoItem > $qtyLpb && $qtyLpb > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial LPB</span>";
				else return "Parsial LPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>Done BPB</span>";
				else return "Done BPB";
			}
		} else if ($statusPo == 6) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Cancel</span>";
			} else {
				return "Cancel";
			}
		} else if ($statusPo == 8) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Revised</span>";
			} else {
				return "Revised";
			}
		} else if ($statusPo == 9) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Revision Draft</span>";
			} else {
				return "Revision Draft";
			}
		} else if ($statusPo == 10) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>PO Draft</span>";
			} else {
				return "PO Draft";
			}
		} else {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}
	}
	else { // Non LPB
		if ($statusPo == 1) {
			if ($raw == null) {
				return "<span class='badge badge-info'>On Progress Approval PO</span>";
			} else {
				return "On Progress Approval PO";
			}
		} else if ($statusPo == 2) {
			if($qtyPoItem <= $qtyBpbLokal){
				if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
				else return "Done BPB";
			}
			else if ($qtyPoItem > $qtyBpbLokal && $qtyBpbLokal > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
				else return "Parsial BPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>PO Issued</span>";
				else return "PO Issued";
			}
		} else if ($statusPo == 3) {
			if ($raw == null) {
				return "<span class='badge badge-purple'>Perbaikan</span>";
			} else {
				return "Perbaikan";
			}
		} else if ($statusPo == 4) {
			if($qtyPoItem <= $qtyBpbLokal){
				if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
				else return "Done BPB";
			}
			else if ($qtyPoItem > $qtyBpbLokal && $qtyBpbLokal > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
				else return "Parsial BPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>Parsial LPB</span>";
				else return "Parsial LPB";
			}
		} else if ($statusPo == 5) {
			if($qtyPoItem <= $qtyBpbLokal){
				if ($raw == null)return "<span class='badge badge-success'>Done BPB</span>";
				else return "Done BPB";
			}
			else if ($qtyPoItem > $qtyBpbLokal && $qtyBpbLokal > 0){
				if ($raw == null)return "<span class='badge badge-primary'>Parsial BPB</span>";
				else return "Parsial BPB";
			}
			else {
				if ($raw == null)return "<span class='badge badge-info'>Done BPB</span>";
				else return "Done BPB";
			}
		} else if ($statusPo == 6) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Cancel</span>";
			} else {
				return "Cancel";
			}
		} else if ($statusPo == 8) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Revised</span>";
			} else {
				return "Revised";
			}
		} else if ($statusPo == 9) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Revision Draft</span>";
			} else {
				return "Revision Draft";
			}
		} else if ($statusPo == 10) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>PO Draft</span>";
			} else {
				return "PO Draft";
			}
		} else {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}
	}
}

function getQtyItemDphByPrItemId($id_item_pr){
    $query = DB::table('dph_items')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'dph_items.pr_item_id')
        ->leftJoin('dph_suppliers','dph_suppliers.id','=','dph_items.dph_supplier_id')
        ->leftJoin('dph','dph.id','=','dph_suppliers.dph_id')
        ->where('dph_items.pr_item_id', '=',$id_item_pr)
        ->whereIn('dph.status',[0,1,2,3,4]) //Onprogress,OnProgApproval,Revisi,Done
        ->where('dph_items.is_recomendation', true)
        ->sum('dph_items.qty');
    return $query;
}

function getQtyItemPoByPrItemId($id_item_pr){
    $query = DB::table('po_items')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->leftJoin('po','po.id','=','po_items.po_id')
        ->where('po_items.pr_item_id', '=',$id_item_pr)
        ->whereIn('po.status',[0,1,2,3,4,5,9,10]) //OnProgApproval,PoIssued,Perbaikan,LPBParsial,Done,RevisionDraft,PODraft,Draft
        ->sum('po_items.qty');
    return $query;
}

function getDphStatusById($id){
    $query = DB::table('dph')->where('id','=',$id)->first();
    return $query->status;
}

function getSubTotalByPoId($idPo){
    $query = DB::table('po_items')->where('po_items.po_id','=',$idPo)->get();
    $subtotal = 0;
    foreach($query as $item){
        $total = $item->price * $item->qty - (($item->price * $item->qty) *  $item->discount / 100);
        $subtotal += $total;
    }
    return $subtotal;
}

function getQtySisaItemPoByIdPoItem($po_itemsId, $poType){
    if($poType == 'lpb'){
        $query = DB::table('lpb_items')
        ->leftJoin('lpb', 'lpb.id', '=', 'lpb_items.lpb_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'lpb_items.po_item_id')
        ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
		->whereNotIn('lpb.status',[3,4])
        ->where('po_items.id', $po_itemsId)
        ->sum('lpb_items.qty');
    }else{
        $query = DB::table('bpb_items')
        ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
        ->leftJoin('po_items', 'po_items.id', '=', 'bpb_items.spb_item_id')
        ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
        ->where('po_items.id', $po_itemsId)
        ->whereIn('bpb.status', [1,2])
        ->sum('bpb_items.qty');
    }
	return $query;
}


// LINIMASA
function getDateId($date)
{
    return Carbon::parse($date)->format('d-M-Y H:i');
}

function getItemDpmById($id)
{
    $query = DB::table('purchase_items as pi')
        ->select(
            'pi.*',
            'purchases.doc_no AS no_dpm',
            'purchases.status AS statusdpm',
            'purchases.type AS typedpm',
            'purchases.created_at AS tgl_dpm',
            'mip.name AS product_name',
            'mip.part_number',
            'd.name AS department',
            'l.name AS location',
            'mib.name AS brand',
            'users.name AS createdbydpm'
        )
        ->leftJoin('master_item_products as mip', 'mip.id', '=', 'pi.product_id')
        ->leftJoin('master_item_brands as mib', 'mip.brand_id', '=', 'mib.id')
        ->leftJoin('purchases', 'purchases.id', '=', 'pi.purchase_id')
        ->leftJoin('departments as d', 'd.id', '=', 'purchases.department_id')
        ->leftJoin('locations as l', 'l.id', '=', 'purchases.location_id')
        ->leftJoin('users','users.id','=','purchases.created_by')
        ->where('pi.id', '=', $id)
        ->first();
    return $query;
}

function getItemPrByIdItemDpm($id)
{
    $query = DB::table('purchase_items as pi')
        ->select(
            'pi.*',
            'purchase_requisitions.doc_no AS no_pr',
            'purchase_requisitions.type AS type_pr',
            'purchase_requisitions.created_at AS tgl_pr'
        )
        ->leftJoin('purchase_requisitions','purchase_requisitions.id','=','pi.pr_id')
        ->where('pi.id', '=', $id)
        ->where('pi.pr_status','=',1)
		->orderBy('purchase_requisitions.id','DESC')
        ->get();
    return $query;
}

function getItemPoByIdItemDpm($id)
{
    $query = DB::table('po_items')
        ->select(
            'po_items.*',
            'po.doc_no AS no_po',
            'po.created_at AS tgl_po',
            'po.status AS statusPo',
            'po.type AS typePo',
        )
        ->leftJoin('po','po.id','=','po_items.po_id')
        ->leftJoin('purchase_items','purchase_items.id','=','po_items.pr_item_id')
        ->where('purchase_items.id', '=', $id)
        ->whereIn('po.status',[1,2,3,4,5,6,8,9,10,0])
		->orderBy('po.id','DESC')
        ->get();
    return $query;
}

function getItemLpbByIdItemDpm($id)
{
    $query = DB::table('lpb_items')
        ->select(
            'lpb_items.*',
            'lpb.doc_no AS no_lpb',
            'lpb.created_at AS tgl_lpb',
            'lpb.status AS statusLpb',
            'lpb.spb_status AS spb_statusLpb',
            'purchase_items.measure AS satuan'
        )
        ->leftJoin('lpb','lpb.id','=','lpb_items.lpb_id')
        ->leftJoin('purchase_items','purchase_items.id','=','lpb_items.pr_item_id')
        ->where('purchase_items.id', '=', $id)
        ->whereIn('lpb.status',[0,1,2,3,4])
		->orderBy('lpb.id','DESC')
        ->get();
    return $query;
}

function getItemSpbByIdItemDpm($id)
{
    $query = DB::table('spb_kolis')
        ->select(
            'spb_kolis.*',
            'spb.doc_no AS no_spb',
            'spb.created_at AS tgl_spb',
            'spb.status AS statusSpb',
            'purchase_items.measure AS satuan'
        )
        ->leftJoin('spb','spb.id','=','spb_kolis.spb_id')
        ->leftJoin('purchase_items','purchase_items.id','=','spb_kolis.pr_item_id')
        ->where('purchase_items.id', '=', $id)
        ->whereIn('spb.status',[0,1,2,3,4])
		->orderBy('spb.id','DESC')
        ->get();
    return $query;
}

function getItemBpbByIdItemDpm($id)
{
    $query = DB::table('bpb_items')
        ->select(
            'bpb_items.*',
            'bpb.doc_no AS no_bpb',
            'bpb.created_at AS tgl_bpb',
            'bpb.status AS statusBpb',
            'purchase_items.measure AS satuan'
        )
        ->leftJoin('bpb','bpb.id','=','bpb_items.bpb_id')
        ->leftJoin('purchase_items','purchase_items.id','=','bpb_items.pr_item_id')
        ->where('purchase_items.id', '=', $id)
        ->whereIn('bpb.status',[0,1,2])
		->orderBy('bpb.id','DESC')
        ->get();
    return $query;
}

function getStatusStepItemDpm($status, $statusDPM = null, $raw = null)
{
	if ($status == 1) {
		if ($statusDPM == 3 ) {
			if ($raw == null) return "<span class='badge badge-warning'>Hold</span>";
			else return "Hold";
		} else if($statusDPM == 11){
            if ($raw == null) return "<span class='badge badge-warning'>On Progress</span>";
			else return "On Progress";
        }
		else {
			if ($raw == null)return "<span class='badge badge-info'>On Progress Approval</span>";
		 	else return "On Progress Approval";
		}
	} else if ($status == 2) {
		if ($raw == null) return "<span class='badge badge-danger'>Rejected</span>";
		else return "Rejected";
	} else if ($status == 3) {
		if ($raw == null)return "<span class='badge badge-danger'>Cancel</span>";
		else return "Cancel";
	} else if ($status == 4) {
		if ($raw == null) return "<span class='badge badge-success'>Done</span>";
        else return "Done";
	} else if ($status == 5) {
		if ($raw == null) return "<span class='badge badge-success'>Done</span>";
		else return "Done";
	} else {
		if ($raw == null)  return "<span class='badge badge-danger'>Undefined</span>";
	 	else return "Undefined";
	}
}

function getStatusStepItemPR($statusPR, $statusPO, $parsial, $type = null, $raw = null)
{
	if ($type == 'po') {
		if ($statusPR == 1 && $statusPO == 0) {
			if ($raw == null) {
				return "<span class='badge badge-info'>On Progress</span>";
			} else {
				return "On Progress";
			}
		}
		if ($statusPR == 1 && $statusPO == 1) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Done</span>";
			} else {
				return "Done";
			}
		}
		if ($statusPR == 1 && $statusPO == 2) {
			if ($raw == null) {
				return "<span class='badge badge-primary'>Partial PR in Progress</span>";
			} else {
				return "Partial PR in Progress";
			}
		}
		if ($statusPR == 1 && $statusPO == 3) {
			if ($parsial <= 0 || $parsial == null) {
				if ($raw == null) {
					return "<span class='badge badge-danger'>Closed</span>";
				} else {
					return "Closed";
				}
			} else {
				if ($raw == null) {
					return "<span class='badge badge-danger'>Partially Closed</span>";
				} else {
					return "Partially Closed";
				}
			}
		}
		if ($statusPR == 1 && $statusPO == 4) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Partially Closed</span>";
			} else {
				return "Partially Closed";
			}
		}
	}else{
		if ($type == 'im') {
			if ($raw == null) {
				return "<span class='badge badge-success'>IM</span>";
			} else {
				return "IM";
			}
		}
        if ($type == 'petty_cash') {
			if ($raw == null) {
				return "<span class='badge badge-success'>PETTY CASH</span>";
			} else {
				return "PETTY CASH";
			}
		}
		else {
            if ($raw == null)  return "<span class='badge badge-danger'>Undefined</span>";
             else return "Undefined";
        }
	}
}

function getStatusStepItemPO($statusPo, $typePo, $lpbStatusItem, $raw = null)
{
	if ($typePo == 'lpb') {
		if ($statusPo == 1) {
			if ($raw == null) {
				return "<span class='badge badge-info'>On Progress Approval</span>";
			} else {
				return "On Progress Approval";
			}
		}else if ($statusPo == 2) {
			if($lpbStatusItem == 1){
                if ($raw == null) {
                    return "<span class='badge badge-success'>Done</span>";
                } else {
                    return "Done";
                }
            }else if($lpbStatusItem == 2){
                if ($raw == null) {
                    return "<span class='badge badge-primary'>Partially Lpb</span>";
                } else {
                    return "Partially Lpb";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-warning'>Po Issued</span>";
                } else {
                    return "Po Issued";
                }
            }
		}
        else if ($statusPo == 3) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Perbaikan</span>";
			} else {
				return "Perbaikan";
			}
		}else if ($statusPo == 4) {
            if($lpbStatusItem == 1){
                if ($raw == null) {
                    return "<span class='badge badge-success'>Done</span>";
                } else {
                    return "Done";
                }
            }else if($lpbStatusItem == 2){
                if ($raw == null) {
                    return "<span class='badge badge-primary'>Partially Lpb</span>";
                } else {
                    return "Partially Lpb";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-warning'>Po Issued</span>";
                } else {
                    return "Po Issued";
                }
            }
		}else if ($statusPo == 5) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Done</span>";
			} else {
				return "Done";
			}
		}else if ($statusPo == 6) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Cancel</span>";
			} else {
				return "Cancel";
			}
		}else if ($statusPo == 8) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Revised Closed</span>";
			} else {
				return "Revised Closed";
			}
		}else if ($statusPo == 9) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Revision Draft</span>";
			} else {
				return "Revision Draft";
			}
		}else if ($statusPo == 10) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}else {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}
	}else{
        if ($statusPo == 1) {
			if ($raw == null) {
				return "<span class='badge badge-info'>On Progress Approval</span>";
			} else {
				return "On Progress Approval";
			}
		}else if ($statusPo == 2) {
			if($lpbStatusItem == 1){
                if ($raw == null) {
                    return "<span class='badge badge-success'>Done</span>";
                } else {
                    return "Done";
                }
            }else if($lpbStatusItem == 2){
                if ($raw == null) {
                    return "<span class='badge badge-primary'>Partially Bpb</span>";
                } else {
                    return "Partially Bpb";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-warning'>Po Issued</span>";
                } else {
                    return "Po Issued";
                }
            }
		}
        else if ($statusPo == 3) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Perbaikan</span>";
			} else {
				return "Perbaikan";
			}
		}else if ($statusPo == 4) {
            if($lpbStatusItem == 1){
                if ($raw == null) {
                    return "<span class='badge badge-success'>Done</span>";
                } else {
                    return "Done";
                }
            }else if($lpbStatusItem == 2){
                if ($raw == null) {
                    return "<span class='badge badge-primary'>Partially Bpb</span>";
                } else {
                    return "Partially Bpb";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-warning'>Po Issued</span>";
                } else {
                    return "Po Issued";
                }
            }
		}else if ($statusPo == 5) {
			if ($raw == null) {
				return "<span class='badge badge-success'>Done</span>";
			} else {
				return "Done";
			}
		}else if ($statusPo == 6) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Cancel</span>";
			} else {
				return "Cancel";
			}
		}else if ($statusPo == 8) {
			if ($raw == null) {
				return "<span class='badge badge-danger'>Revised Closed</span>";
			} else {
				return "Revised Closed";
			}
		}else if ($statusPo == 9) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Revision Draft</span>";
			} else {
				return "Revision Draft";
			}
		}else if ($statusPo == 10) {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}else {
			if ($raw == null) {
				return "<span class='badge badge-warning'>Draft</span>";
			} else {
				return "Draft";
			}
		}
	}
}

function getStatusStepItemLpb($statusItem = null, $statusLpb = null, $spb_statusLpb = null, $raw = null)
{
	if ($statusLpb == 1){
        if($spb_statusLpb == 1){
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }elseif($spb_statusLpb == 2){
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }else{
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }
	}elseif($statusLpb == 2){
        if($spb_statusLpb == 1){
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }elseif($spb_statusLpb == 2){
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }else{
            if($statusItem == 1){
                //DONE SPB
                if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
                else return "Done";
            }elseif($statusItem == 2){
                //PARSIAL SPB
                if ($raw == null) return "<span class='badge badge-primary'>Partially Spb</span>";
                else return "Partially Spb";
            }else{
                //BELUM SPB
                if ($raw == null) return "<span class='badge badge-info'>On Progress</span>";
                else return "On Progress";
            }
        }
    }elseif($statusLpb == 3){
        // CLOSE
        if ($raw == null)  return "<span class='badge badge-danger'>Close</span>";
        else return "Close";
    }elseif($statusLpb == 4){
        //REVERSAL
        if ($raw == null)  return "<span class='badge badge-danger'>Reversal</span>";
        else return "Reversal";
    }else {
        //DRAFT
		if ($raw == null)  return "<span class='badge badge-warning'>Draft</span>";
	 	else return "Draft";
	}
}

function getStatusStepItemSpb($bpb_statusItem = null, $statusSpb = null, $raw = null)
{
	if ($statusSpb == 1){
        //PUBLISHED
        if($bpb_statusItem == 1){
            if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
            else return "Done";
        }elseif($bpb_statusItem == 2){
            if ($raw == null)  return "<span class='badge badge-primary'>Partially Bpb</span>";
            else return "Partially Bpb";
        }else{
            if ($raw == null)  return "<span class='badge badge-info'>On Progress</span>";
            else return "On Progress";
        }
	}elseif($statusSpb == 2){
        // BPB PARSIAL
        if($bpb_statusItem == 1){
            if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
            else return "Done";
        }elseif($bpb_statusItem == 2){
            if ($raw == null)  return "<span class='badge badge-primary'>Partially Bpb</span>";
            else return "Partially Bpb";
        }else{
            if ($raw == null)  return "<span class='badge badge-info'>On Progress</span>";
            else return "On Progress";
        }
    }elseif($statusSpb == 3){
        // BPB DONE
        if($bpb_statusItem == 1){
            if ($raw == null)  return "<span class='badge badge-success'>Done</span>";
            else return "Done";
        }elseif($bpb_statusItem == 2){
            if ($raw == null)  return "<span class='badge badge-primary'>Partially Bpb</span>";
            else return "Partially Bpb";
        }else{
            if ($raw == null)  return "<span class='badge badge-info'>On Progress</span>";
            else return "On Progress";
        }
    }elseif($statusSpb == 4){
        // REVERSAL
        if ($raw == null)  return "<span class='badge badge-danger'>Reversal</span>";
        else return "Reversal";
    }else {
        //DRAFT
		if ($raw == null)  return "<span class='badge badge-warning'>Draft</span>";
	 	else return "Draft";
	}
}
function getStatusStepItemBpb($statusBpb = null, $raw = null)
{
	if ($statusBpb == 1) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Done</span>";
		} else {
			return "Done";
		}
	} else if ($statusBpb == 2) {
		if ($raw == null) {
			return "<span class='badge badge-success'>Draft</span>";
		} else {
			return "Draft";
		}
	} else {
		if ($raw == null) {
			return "<span class='badge badge-warning'>Draft</span>";
		} else {
			return "Draft";
		}
	}
}

function getProductItemPo($id){
    $query = DB::table('po_items')
        ->select('po_items.*','purchase_items.qty_parsial', 'purchase_items.flag AS flag', 'purchase_items.needed_on_date', 'purchase_items.qty AS qty_pr', 'purchase_items.po_status',
        'master_item_products.name AS product',
        'master_item_products.code AS productCode', 'master_item_products.part_number AS productPartNumber','master_item_brands.name AS productBrand')
        ->leftJoin('master_item_products', 'master_item_products.id', '=', 'po_items.product_id')
        ->leftJoin('master_item_brands', 'master_item_products.brand_id', '=', 'master_item_brands.id')
        ->leftJoin('purchase_items', 'purchase_items.id', '=', 'po_items.pr_item_id')
        ->where('po_items.po_id', $id)
        ->orderBy('pr_item_id','ASC')
        ->get();
    return $query;
}

function gethistoryPo($id){
    $query =  DB::table('po_histories')
    ->select('po_histories.*','users.name AS employee')
    ->leftJoin('users', 'users.id', '=', 'po_histories.user_id')
    ->where('po_histories.po_id', $id)
    ->orderBy('po_histories.created_at', 'DESC')
    ->get();
    return $query;
}

function getQtyAllBpbItemBySpbKolis($id_spb_kolis){
	$query = DB::table('bpb_items')
	->select('bpb_items.qty')
	->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
	->where('bpb_items.spb_item_id', '=', $id_spb_kolis)
	->whereIn('bpb.status', [1,2])
    ->whereNull('bpb.po_id',)
	->sum('bpb_items.qty');
	return $query;
}


function getStatusUserAsset($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null)  return "<span class='badge badge-primary'>Aktif</span>";
		else return "Aktif";
	}else {
		if ($raw == null) return "<span class='badge badge-danger'>Tidak Aktif</span>";
		else return "Tidak Aktif";
	}
}

function getStatusInventoryAsset($status, $raw = null)
{
	if ($status == 1) {
		if ($raw == null)  return "<span class='badge badge-primary'>Publish</span>";
		else return "Publish";
	}else if ($status == 2) {
		if ($raw == null)  return "<span class='badge badge-danger'>Deleted</span>";
		else return "Deleted";
	}else {
		if ($raw == null) return "<span class='badge badge-warning'>Draft</span>";
		else return "Draft";
	}
}

function getStatusDia($id, $raw = null){
    $statuses = DB::table('inventory_assets')
        ->where('parent_inventory_asset_id','=', $id)
        ->whereNull('deleted_at')
        ->pluck('status')
        ->toArray();
    $ada_0 = in_array(0, $statuses);
    $ada_1 = in_array(1, $statuses);

    if ($ada_0 && $ada_1) {
        if ($raw == null) return "<span class='badge badge-primary'>Parsial Publish</span>";
		else return "Parsial Publish";
    }elseif ($ada_0){
        if ($raw == null) return "<span class='badge badge-warning'>Draft</span>";
		else return "Draft";
    } elseif ($ada_1) {
        if ($raw == null) return "<span class='badge badge-success'>Done</span>";
		else return "Done";
    } else {
        if ($raw == null) return "<span class='badge badge-danger'>Undefined </span>";
		else return "Undefined ";
    }
}

function getDataRelationAst($type = null, $id = null)
{
    $result = [];
    if($type == 'po'){
        $result = DB::table('po_items')
            ->leftJoin('po', 'po.id', '=', 'po_items.po_id')
            ->where('po_items.id', $id)
            ->select('po.doc_no', 'po_items.id')
            ->groupBy('po.doc_no', 'po_items.id')
            ->first();
    }elseif($type == 'bpb'){
        $result = DB::table('bpb_items')
            ->leftJoin('bpb', 'bpb.id', '=', 'bpb_items.bpb_id')
            ->where('bpb_items.id', $id)
            ->select('bpb_items.id', 'bpb.doc_no')
            ->groupBy('bpb.doc_no', 'bpb_items.id')
            ->first();
    }
    return $result;
}
function getHistoryInvAsset($idinvasset){
    $query = DB::table('inventory_asset_histories')
        ->select('inventory_asset_histories.*')
        ->where('inventory_asset_histories.inventory_asset_id','=',$idinvasset)
        ->orderBy('inventory_asset_histories.created_at','DESC')
        ->get();
    return $query;
}
function formatDurasiLengkap($startDate, $endDate)
{
	if (!$startDate || !$endDate) {
		return '-';
	}

	$start = Carbon::parse($startDate);
	$end = Carbon::parse($endDate);
	$diff = $start->diff($end);

	$parts = [];

	if ($diff->y) $parts[] = $diff->y . ' tahun';
	if ($diff->m) $parts[] = $diff->m . ' bulan';
	if ($diff->d) $parts[] = $diff->d . ' hari';
	if ($diff->h) $parts[] = $diff->h . ' jam';
	if ($diff->i) $parts[] = $diff->i . ' menit';

	return implode(' ', $parts);
}

function getCompanyByLocationId($id)
{
	$company = DB::table('companies')
		->select('companies.*')
        ->leftJoin('locations','locations.company_id','=','companies.id')
		->where('locations.id', $id)
		->first();
	if ($company) {
		return $company;
	} else {
		return "";
	}
}

function getLocationByID($id)
{
	$location = DB::table('locations')
		->select('locations.*')
		->where('id', $id)
		->first();
    return $location;
}
function getTypeWto($type = null){
    if($type == 1){
        return 'PEMINJAMAN';
    }elseif($type == 0){
        return 'PEMINDAHAN';
    }elseif($type == 2){
        return 'PENJUALAN';
    }elseif($type == 3){
        return 'PENGEMBALIAN';
    }else{
        return 'Type Tidak Diketahui';
    }
}
function getStatusTransferIn($status,$type,$type_status, $raw = null)
{
    if($type == 1){ // PEMINJAMAN
        if ($status == 1) {
            if($type_status == 0){
                if ($raw == null) {
                    return "<span class='badge badge-info'>Published</span>";
                } else {
                    return "Published";
                }
            }else{
                if ($raw == null) {
                    return "<span class='badge badge-success'>Done</span>";
                } else {
                    return "Done";
                }
            }
        } elseif ($status == 2) {
            if ($raw == null) {
                return "<span class='badge badge-primary'>In Progress Checking</span>";
            } else {
                return "In Progress Checking";
            }
        } else {
            if ($raw == null) {
                return "<span class='badge badge-warning'>Unknow</span>";
            } else {
                return "Unknow";
            }
        }
    }else{
        if ($status == 1) {
            if ($raw == null) {
                return "<span class='badge badge-success'>Done</span>";
            } else {
                return "Done";
            }
        } elseif ($status == 2) {
            if ($raw == null) {
                return "<span class='badge badge-primary'>In Progress Checking</span>";
            } else {
                return "In Progress Checking";
            }
        } else {
            if ($raw == null) {
                return "<span class='badge badge-warning'>Unknow</span>";
            } else {
                return "Unknow";
            }
        }
    }
}
function getHistoryRequest($produkId = null,$locationId = null){
    $result = DB::table('po_items')
        ->select('po.doc_no AS nopo','bpb.created_at AS tglpenerimaan')
        ->leftJoin('po','po.id','=','po_items.po_id')
        ->leftJoin('purchase_items','po_items.pr_item_id','=','purchase_items.id')
        ->leftJoin('purchases','purchase_items.purchase_id','=','purchases.id')
        ->leftJoin('bpb_items','bpb_items.pr_item_id','=','purchase_items.id')
        ->leftJoin('bpb','bpb.id','=','bpb_items.bpb_id')
        ->where('po.status','=','5')
        ->where('purchases.location_id','=',$locationId)
        ->where('purchase_items.product_id','=',$produkId)
        ->whereNotNull('bpb_items.id')
        ->orderBy('po.id','DESC')
        ->orderBy('bpb.id','DESC')
        ->first();
    return $result;
}
function getStatusPC($status, $raw = null)
{
    $status = strtolower((string) $status);

    if ($status === '1') {
        return $raw ? "On Progress Checking" : "<span class='badge badge-info'>On Progress Checking</span>";
    }
    else if ($status === '0') {
        return $raw ? "Draft" : "<span class='badge badge-warning'>Draft</span>";
    }
    else if ($status === '2') {
        return $raw ? "Done" : "<span class='badge badge-success'>Done</span>";
    }
    else if ($status === '3') {
        return $raw ? "Rejected" : "<span class='badge badge-danger'>Rejected</span>";
    }
    else if ($status === '4') {
        return $raw ? "Pending Completeness" : "<span class='badge badge-primary'>Pending Completeness</span>";
    }
    else if ($status === '5') {
        return $raw ? "PO Relation Changed" : "<span class='badge badge-danger'>PO Relation Changed</span>";
    }
    else {
        return $raw ? "Unknown" : "<span class='badge badge-secondary'>Unknown</span>";
    }
}

function getTypePC($type, $raw = null)
{
    $type = strtolower((string) $type);

    if ($type === '1') {
        return $raw ? "TEMPO" : "<span class='badge badge-primary'>TEMPO</span>";
    }
    else if ($type === '2') {
        return $raw ? "CBD / COD / DP" : "<span class='badge badge-warning'>CBD / COD / DP</span>";
    }
    else {
        return $raw ? "Unknown" : "<span class='badge badge-secondary'>Unknown</span>";
    }
}

function getTypePaymentTerms($type, $raw = null)
{
    $type = strtolower((string) $type);

    if ($type === '0') {
        return $raw ? "Tempo" : "<span class='badge badge-primary'>Tempo</span>";
    }
    else if ($type === '1') {
        return $raw ? "CBD / COD / DP" : "<span class='badge badge-warning'>CBD / COD / DP</span>";
    }
    else {
        return $raw ? "Unknown" : "<span class='badge badge-secondary'>Unknown</span>";
    }
}

function checkIssetPC($idpo) {
    $query = DB::table('payment_completions')
        ->where('po_id', '=', $idpo)
        ->where('status', '!=', 3)
        ->first();

    return $query ? true : false;
}