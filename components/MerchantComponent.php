<?php
namespace app\components;
use yii;
use yii\base\Component;
use \app\helpers\Utility;
use \app\models\MerchantNotifications;

 date_default_timezone_set("asia/kolkata");

class MerchantComponent extends Component{

    public function init(){
        date_default_timezone_set("asia/kolkata");
        parent::init();
    }
    public function deductstockfrominventory($arr)
    {
        
        $sqlOrderDetail = 'select ingredient_id,ingred_quantity,ingred_units,count as item_order_count,mr.product_id  
        from order_products op 
        left join 
			merchant_recipe mr on op.product_id = mr.product_id
			where order_id = \''.$arr['order_id'].'\'';
			$orderDetail = Yii::$app->db->createCommand($sqlOrderDetail)->queryAll();

			if(count($orderDetail) > 0)
			{
				$sqlStock = 'select * from ingredient_stock_register isr where merchant_id = \''.Yii::$app->user->identity->merchant_id.'\'
				and  created_on = \''.date('Y-m-d').'\'';
				$resStock = Yii::$app->db->createCommand($sqlStock)->queryAll();
				$stockVsingredient = array_column($resStock,'closing_stock','ingredient_id');
				$oldStockOutVsingredient = array_column($resStock,'stock_out','ingredient_id');
				$stockIngredients = array_column($resStock,'ingredient_id');
				
				if(count($resStock) > 0)
				{
					for($i=0;$i<count($orderDetail);$i++)
					{
						if(in_array($orderDetail[$i]['ingredient_id'],$stockIngredients))
						{
							$sqlCuurentIngredientStock = 'select * from ingredient_stock_register where merchant_id = \''.Yii::$app->user->identity->merchant_id.'\'
							and created_on =\''.date('Y-m-d').'\' and ingredient_id = \''.$orderDetail[$i]['ingredient_id'].'\'';
							$resCurrentIngredientStock = Yii::$app->db->createCommand($sqlCuurentIngredientStock)->queryOne();
							$ingredientClosingStock = $resCurrentIngredientStock['closing_stock'];
							if($orderDetail[$i]['ingred_units'] == '1' || $orderDetail[$i]['ingred_units'] == '2'){
								$outIngredQty = $orderDetail[$i]['ingred_quantity'] * 1000;
							}
							else{
								$outIngredQty = $orderDetail[$i]['ingred_quantity'];
							}
							$newStockOut = ($orderDetail[$i]['item_order_count'] * ($outIngredQty)); //stock out in grams
							$newIngredClosingStock = $ingredientClosingStock - $newStockOut;
							if($newIngredClosingStock >= 0){
								$newIngredClosingStock = $newIngredClosingStock;
								$newStockOut = $newStockOut;
							}else{
								$newIngredClosingStock = 0;
								$newStockOut = $ingredientClosingStock;
							}
						$sqlStockUpdate = 'update ingredient_stock_register set stock_out = \''.($resCurrentIngredientStock['stock_out']+$newStockOut).'\'
						,closing_stock = \''.$newIngredClosingStock.'\' where merchant_id = \''.Yii::$app->user->identity->merchant_id.'\'
						and	ingredient_id =\''.$orderDetail[$i]['ingredient_id'].'\' and created_on = \''.date('Y-m-d').'\'';
						$resStockUpdate = Yii::$app->db->createCommand($sqlStockUpdate)->execute();
						
						//Stock Alert Notifications 
						
						$sqlIngredientDet = 'select * from ingredients where ID=\''.$orderDetail[$i]['ingredient_id'].'\'';
						$ingredientDet = Yii::$app->db->createCommand($sqlIngredientDet)->queryOne();
						if($ingredientDet['stock_alert'] > ($newIngredClosingStock/1000)){
						    $message = $ingredientDet['item_name'].' stock is less available stock is '.$newIngredClosingStock. ' on '.date('d-M-Y h:i:s A');
						    $stockNotiArr = ['merchant_id'=>Yii::$app->user->identity->merchant_id, 'message'=>$message,'seen'=>'0'];
						    $stockNotiStatus = $this->addMerchantNotifications($stockNotiArr);
						}
						
						//--Stock Alert Notifications-- 
						
						$sqlPurchase = 'select ipd.ID,purchase_qty_units,purchase_price,used_qty,remaining_qty,ingredient_id 
						from ingredient_purchase ip 
						inner join ingredient_purchase_detail ipd on ip.ID = ipd.purchase_id 
						where merchant_id = \''.Yii::$app->user->identity->merchant_id.'\' 
						and ipd.ingredient_id = \''.$orderDetail[$i]['ingredient_id'].'\' and ipd.remaining_qty > 0
						order by reg_date';
						$resPurchase = Yii::$app->db->createCommand($sqlPurchase)->queryAll();
						
						$knockOffNewStockOut = $newStockOut;
						for($p=0;$p<count($resPurchase);$p++)
						{
							
							$remaining_qty_start = $resPurchase[$p]['remaining_qty'];	
							
							if($remaining_qty_start >= $knockOffNewStockOut)
							{
								$sqlKnockOff_one = 'update ingredient_purchase_detail set
								used_qty = \''.(($resPurchase[$p]['used_qty']??0)+$knockOffNewStockOut).'\'
								,remaining_qty = \''.($remaining_qty_start - $knockOffNewStockOut).'\' 
								where ID = \''.$resPurchase[$p]['ID'].'\'';
								$resKnockOff_one = Yii::$app->db->createCommand($sqlKnockOff_one)->execute();
								$merchantOrderRecArr = [
								'merchant_id' => Yii::$app->user->identity->merchant_id
								,'order_id'=>$arr['order_id']
								,'product_id'=>$orderDetail[$i]['product_id']
								,'ingredi_id'=>$orderDetail[$i]['ingredient_id']
								,'ingredi_name'=>$resCurrentIngredientStock['ingredient_name']
								,'ingredi_qty'=>$knockOffNewStockOut
								,'ingredi_price'=>($resPurchase[$p]['purchase_price']/$resPurchase[$p]['purchase_qty_units']) * $knockOffNewStockOut
								,'ingredi_detail_id'=>$resPurchase[$p]['ID']
								,'reg_date'=>date('Y-m-d h:i:s')
								];
								$merchantOrderRec = new \app\models\MerchantOrderRecipeCost;
							$merchantOrderRec->attributes = $merchantOrderRecArr;
							$merchantOrderRec->save();
							
							break;								
							}
							else{

								$sqlKnockOff_one = 'update ingredient_purchase_detail set used_qty = \''.(($resPurchase[$p]['used_qty']??0)+$remaining_qty_start).'\'
								,remaining_qty = \''.'0'.'\' where ID = \''.$resPurchase[$p]['ID'].'\'';
								$resKnockOff_one = Yii::$app->db->createCommand($sqlKnockOff_one)->execute();
								$knockOffNewStockOut = $knockOffNewStockOut - $remaining_qty_start;
								$merchantOrderRecArr = [
								'merchant_id' => Yii::$app->user->identity->merchant_id
								,'order_id'=>$arr['order_id'],'product_id'=>$orderDetail[$i]['product_id']
								,'ingredi_id'=>$orderDetail[$i]['ingredient_id']
								,'ingredi_name'=>$resCurrentIngredientStock['ingredient_name']
								,'ingredi_qty'=>$remaining_qty_start
								,'ingredi_price'=>($resPurchase[$p]['purchase_price']/$resPurchase[$p]['purchase_qty_units']) * $remaining_qty_start
								,'ingredi_detail_id'=>$resPurchase[$p]['ID']
								,'reg_date'=>date('Y-m-d h:i:s')
								];
								print_r($merchantOrderRecArr);exit;
								$merchantOrderRec = new \app\models\MerchantOrderRecipeCost;
							$merchantOrderRec->attributes = $merchantOrderRecArr;
							$merchantOrderRec->save();
							}
							
						}
						
						
						}
					}	
				}
			}

    }
    public function addMerchantNotifications($inputArr){
        $model = new MerchantNotifications;
        $model->attributes = $inputArr;
        $model->created_on = date('Y-m-d H:i:s');
        $model->created_by = Yii::$app->user->identity->merchant_id;
        if($model->validate()){
            if($model->save()){
                //echo 'saved';
                Yii::trace('merchant notification saved');
            } else {
                //echo 'not saved';
                Yii::trace('merchant notification not saved');
            }
        }
    }
    public function userCreation($customer_mobile,$customer_name =''){
		$modelUser = new \app\models\Users;
		$sqlprevuser = 'select MAX(ID) as id from users';
		$prevuser = Yii::$app->db->createCommand($sqlprevuser)->queryOne();
		$newid = $prevuser['id']+1;
			$modelUser->unique_id = 'FDQ'.sprintf('%06d',$newid);
			$modelUser->name = ucwords($customer_name);
			$modelUser->mobile = trim($customer_mobile); 	
			$modelUser->password = password_hash('112233',PASSWORD_DEFAULT); 	
			$modelUser->status = '1';
			$modelUser->referral_code = 'REFFDQ'.$newid;
			$modelUser->reg_date = date('Y-m-d h:i:s');
			if($modelUser->validate()){
			$modelUser->save();	
			}
			else{
			print_r($modelUser->getErrors());exit;	
			}
			
			return $modelUser['ID']; 
	}
	public function send_sms($mobile,$message){
	    $merchantId = Yii::$app->user->identity->merchant_id;
	    $merchant = \app\models\Merchant::findOne($merchantId);
	    echo '<pre>';
	    echo $merchant['allocated_msgs'].'>'.$merchant['used_msgs'];
	    if($merchant['allocated_msgs'] > $merchant['used_msgs']){
	        \app\helpers\Utility::send_sms($mobile,$message);    
	        $sqlUpdate = 'update merchant set used_msgs = \''.($merchant['used_msgs']+1).'\' where ID = \''.$merchantId.'\'';
		    $resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();   
		    echo $mobile.'-'.$message;
		    
	    } else {
	       // echo 'Message not sent';
	    }
	}
	public function saveorder($arr)
	{
		//echo "<pre>";print_r($arr);exit;
		$userid = $arr['user_id'];
		$selectedpilot = $arr['pilotid'];
		$merchantid = (string)Yii::$app->user->identity->merchant_id;
			$model = new \app\models\Orders;
			$model->merchant_id = $merchantid;
			$model->tablename = $arr['tableid'];
			$model->user_id = $userid;
			$model->serviceboy_id  = $selectedpilot ;
			$model->order_id = Utility::order_id($merchantid,'order'); 
			$model->txn_id = Utility::order_id($merchantid,'transaction');
			$model->txn_date = date('Y-m-d H:i:s');
			$model->amount = $arr['amount'] ? number_format($arr['amount'], 2, '.', ',') : 0;
			
					$model->tax = (string)$arr['taxamt'];
					$model->tips = number_format($arr['tipamt'], 2, '.', '');
					$model->subscription = (string)$arr['subscriptionamt'];
					$model->couponamount = (string)$arr['couponamount'];
					$model->totalamount = (string)$arr['totalamt'];
					$model->coupon = $arr['merchant_coupon'];
					$model['paymenttype'] = $arr['payment_mode'];
							$model->orderprocess = '1';
							$model->status = '1';
							$model->paidstatus = '0';
							$model->paymentby = '1';
							$model->ordertype = 2;
							$sqlprevmerchnat = 'select max(orderline) as id from orders where merchant_id = \''.$merchantid.'\' and date(reg_date) =  \''.date('Y-m-d').'\''; 
							$resprevmerchnat = Yii::$app->db->createCommand($sqlprevmerchnat)->queryOne();
							$prevmerchnat = $resprevmerchnat['id']; 
							$newid = $prevmerchnat>0 ? $prevmerchnat+1 : 100;  
							$model->orderline = (string)$newid;
						    $model->reg_date = date('Y-m-d h:i:s');
						    $model->discount_type = $arr['discount_mode'];
						    $model->discount_number = $arr['merchant_discount'] ??  0;
						  //  echo "<pre>";
						  //  print_r($model);exit;
						  
					if($model->save()){
						
						$orderTransaction = new \app\models\OrderTransactions;
						$orderTransaction->order_id = (string)$model->ID;
						$orderTransaction->user_id = $userid;			
						$orderTransaction->merchant_id = $merchantid;
						$orderTransaction->amount = !empty($arr['amount']) ? number_format(trim($arr['amount']),2, '.', ',') : 0; 
						$orderTransaction->couponamount =   (string)$arr['couponamount']; 
						$orderTransaction->tax =  !empty($arr['taxamt']) ? number_format(trim($arr['taxamt']),2, '.', ',') : 0; 
						$orderTransaction->tips =  !empty($arr['tipamt']) ? number_format(trim($arr['tipamt']),2, '.', ',') : '0'; 
						$orderTransaction->subscription =  !empty($arr['subscriptionamt']) ? number_format(trim($arr['subscriptionamt']),2, '.', ',') : '0'; 
						$orderTransaction->totalamount =   !empty($arr['totalamt']) ? number_format(trim($arr['totalamt']),2, '.', ',') : 0; 
						$orderTransaction->paymenttype = $arr['payment_mode'];
						$orderTransaction->reorder= '0';
						$orderTransaction->paidstatus = '0';
						$orderTransaction->reg_date = date('Y-m-d h:i:s');
						$orderTransaction->save();
						
						
							$productscount = []; $p=0;$r=1;
							foreach($arr['priceind'] as $priceind )
							{
								$productscount[$p]['order_id'] = $model->ID;
											$productscount[$p]['user_id'] = $userid;
											$productscount[$p]['merchant_id'] = $merchantid;
											$productscount[$p]['product_id'] = trim($arr['itemid'][$p]);
											$productscount[$p]['count'] = trim($arr['qtyitem'][$p]);
											$productscount[$p]['price'] = trim($arr['priceind'][$p]);
											$productscount[$p]['inc'] = $r;
											$productscount[$p]['reorder'] = '0';
											$productscount[$p]['reg_date'] = date('Y-m-d h:i:s');
							$p++;$r++;
							}
							Yii::$app->db
							->createCommand()
							->batchInsert('order_products', ['order_id','user_id','merchant_id','product_id', 'count'
							, 'price','inc','reorder','reg_date'],$productscount)
							->execute();

							$tableUpdate = \app\models\Tablename::findOne($arr['tableid']);
										$tableUpdate->table_status = '1';
										$tableUpdate->current_order_id = $model->ID;
										$tableUpdate->save();
							
							$cur_order_id = $model->ID;	
							return $cur_order_id;
					}
	}
}
?>