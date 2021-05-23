<?php
namespace app\components;
use yii;
use yii\base\Component;
use \app\helpers\Utility;
use \app\helpers\MyConst;
 date_default_timezone_set("asia/kolkata");

class EnduserComponent extends Component{

    public function init(){
     date_default_timezone_set("asia/kolkata");
     parent::init();
    }

    public function display($content=null){
        if($content!=null){
            $this->content= $content;
        }
        echo Html::encode($this->content);
    }
	public function bannerlist($val){
		$sqlmerchantsarray = 'select * from banners where status = \'1\'';
		$merchantsarray = Yii::$app->db->createCommand($sqlmerchantsarray)->queryAll();
					 if(!empty($merchantsarray)){
							$merchantlist = $merchants = array();
							foreach($merchantsarray as $merchantsdata){  
							$merchants['image'] = !empty($merchantsdata['image']) ? BANNER_IMAGE.$merchantsdata['image'] : ''; 
								$merchantlist[] = $merchants;
							}
						
							$payload = array("status"=>'1',"bannerlist"=>$merchantlist);
					}else{
						
						$payload = array("status"=>'0',"text"=>"Invalid user");
					 }
					 return $payload;
	}
	public function registration($val) {
	    Yii::trace('=====reg====='.json_encode($val));
		if(!empty($val['name'])&&!empty($val['email'])){ 
			$userarray = array();	
			$sqlprevmerchnat = "select max(ID) as id from users";
			$resprevmerchnat = Yii::$app->db->createCommand($sqlprevmerchnat)->queryOne();
			$prevmerchnat = $resprevmerchnat['id'];
			$newid = $prevmerchnat+1;
			$userarray['unique_id'] = 'FDQ'.sprintf('%06d',$newid);
			$userarray['otp'] = (string)rand(1111,9999);
			$userarray['name'] = ucwords(trim($val['name']));
			$userarray['email'] = trim($val['email']);
			$userarray['mobile'] = trim($val['mobile']); 	
			$userarray['password'] = password_hash(trim($val['password']),PASSWORD_DEFAULT); 	
			$userarray['status'] = '0';
			$userarray['date_of_birth'] = $val['date_of_birth'];
			$userarray['referral_code'] = 'REFFDQ'.$newid;
			$userarray['refered_code'] = $val['refered_code'];
			$userarray['latitude'] = $val['latitude'];
			$userarray['longitude'] = $val['longitude'];
			$sqlrow = "SELECT * FROM users WHERE email = '".$userarray['email']."' and status = '1'";
			$row = Yii::$app->db->createCommand($sqlrow)->queryOne();
			
			if(empty($row['ID'])){
				$sqlrow = "SELECT * FROM users WHERE mobile = '".$userarray['mobile']."' and status = '1'";
				$row = Yii::$app->db->createCommand($sqlrow)->queryOne();
				if(empty($row['ID'])){  
					$sqlalreadyid = "SELECT * FROM users WHERE mobile = '".$userarray['mobile']."' or email = '".$userarray['email']."'
					and status = '0'";
					$alreadyid = Yii::$app->db->createCommand($sqlalreadyid)->queryOne();
					if(empty($alreadyid)){
						//$result = insertQuery($userarray,'users');
						$result = new \app\models\Users;
						$result->attributes =  $userarray;
						$result->reg_date = date('Y-m-d h:i:s');
						$result->mod_date = date('Y-m-d h:i:s');
						$result->save();
						if($result->save()){
						$message = "Hi ".$userarray['name']." ".$userarray['otp']." is your otp for verification.";
						 \app\helpers\Utility::otp_sms($userarray['mobile'],$message);
							$sqluserdetails = "select ID from users where unique_id = '".$userarray['unique_id']."'"; 
							$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
						$payload = array("status"=>'1',"usersid"=>$userdetails['ID'],"text"=>"Account created");	  
						}else{
							$payload = array("status"=>'0',"text"=>$result);
						} 
					}else{
						$message = "Hi ".$userarray['name']." ".$userarray['otp']." is your otp for verification.";
						 \app\helpers\Utility::otp_sms($userarray['mobile'],$message);
							$sqluserdetails = "select ID from users where unique_id = '".$alreadyid['unique_id']."'";
							$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
						$payload = array("status"=>'1',"usersid"=>$alreadyid['ID'],"text"=>"Account created");	  
					}
					
				}else{
					$payload = array("status"=>'0',"text"=>"Mobile already exists please try another");
				}
			}else{
				$payload = array("status"=>'0',"text"=>"Email already exists please try another");
			}
		}else{
			$payload = array('status'=>'0','message'=>'Invalid Parameters');
		}
		return $payload;
	}
	public function registerotp($val)
	{
		if(!empty($val['usersid'])){
		  $usersid =  trim($val['usersid']);
				$sqluserdetails = "select ID,otp from users where ID = '".$usersid."'";
				$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
			if(!empty($userdetails['ID'])){ 
						$otp = $val['otp'];
						if($userdetails['otp']==$otp){ 
							$sqlUpdate = "update users set status = '1' where ID = '".$userdetails['ID']."'";
							$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
							
							$payload = array("status"=>'1',"usersid"=>$userdetails['ID'],"text"=>"OTP Verified");
						}else{
					
							$payload = array("status"=>'0',"text"=>"Invalid OTP");
						}  
			}else{
					
				$payload = array("status"=>'0',"text"=>"Invalid user");
			}
		}else{
					
			$payload = array('status'=>'0','message'=>'Invalid Parameters');
		}
		return $payload;
	}
	public function resendotp($val) {
		if(!empty($val['usersid'])){
		  $usersid =  trim($val['usersid']);
				$sqluserdetails = "select * from users where ID = '".$usersid."'";
				$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
			if(!empty($userdetails['ID'])){ 
							$message = "Hi ".$userdetails['name']." ".$userdetails['otp']." is your otp for verification.";
							\app\helpers\Utility::otp_sms($userdetails['mobile'],$message);
							$payload = array("status"=>'1',"text"=>"OTP Sent Successfully"); 
			}else{
					
				$payload = array("status"=>'0',"text"=>"Invalid user");
			}
		}else{
					
			$payload = array('status'=>'0','message'=>'Invalid Parameters');
		}
		return $payload;
	}
    public function forgotpassword($val) {
        Yii::trace("====forgot password======".json_encode($val));
		$username = $val['username']; 
		if(!empty($username)){ 
			if (filter_var($username, FILTER_VALIDATE_EMAIL)) {
					$sqlrow = "SELECT * FROM users WHERE email = '".$username."'";
			} else {
					$sqlrow = "SELECT * FROM users WHERE mobile = '".$username."'";
			}
					$row = Yii::$app->db->createCommand($sqlrow)->queryOne();
			if(!empty($row)){
					$userarray = $userwherearray = array();
					$otp = rand(1111,9999);
					$userarray['otp'] = $otp;
					$userwherearray['ID'] = $row['ID'];
					$sqlresult = 'update users set otp = \''.$userarray['otp'].'\' where ID = \''.$userwherearray['ID'].'\'';
					$result = Yii::$app->db->createCommand($sqlresult)->execute();
					if($result){ 
					$headers = 'MIME-Version: 1.0' . "\r\n";

								$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

								$headers .= 'From:  info <'.MAILID.'>' . " \r\n" .

											'Reply-To:  '.MAILID.' '."\r\n" .

											'X-Mailer: PHP/' . phpversion();
											 
						$emailmessage = '';
						$emailmessage .= '<p>Dear '.ucwords($row['name']).',</p>';
						$emailmessage .= '<br />';
						$emailmessage .= '<p>You have recevied otp for reset password for your account '.$row['email'].'</p>';
						$emailmessage .= '<br />';
						$emailmessage .= '<p>Enter the below verification code to reset the password.</p>';
						$emailmessage .= '<br />';
						$emailmessage .= '<strong>Verification code : '.$otp.'</strong>';
						$emailmessage .= '<br />';
						$emailmessage .= '<p>Thank you.</p>';
						$subject = 'Forgot password for FoodQ';
						$email = $row['email'];
						$result = mail($email,$subject,$emailmessage,$headers);
						if($result){
						            Yii::trace("====forgot password======".$userarray['otp']);
							$message = "Hi ".$row['name']." ".$userarray['otp']." is your otp for Forgot password.";
							\app\helpers\Utility::otp_sms($row['mobile'],$message);
						 
						
						$payload = array("status"=>'1',"usersid"=>$row['ID'],"text"=>"OTP Sent successfully");
						}else{
						
							$payload = array("status"=>'0',"text"=>"Please try again");
						} 
					}else{
					
						$payload = array("status"=>'0',"text"=>$result);
					} 
			}else{
					
				$payload = array("status"=>'0',"text"=>"Invalid User details");
			} 
		}else{
				
			$payload = array("status"=>'0',"text"=>"Please enter the password");
		}
		return $payload;
	}
    public function forgotpasswordotp($val) 
	{
	    Yii::trace('=====forgetpasswordotp===val=='.json_encode($val));
		if(!empty($val['usersid'])){
		  $usersid =  trim($val['usersid']);
				$sqluserdetails = "select ID,otp from users where ID = '".$usersid."'";
				$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
				if(!empty($userdetails['ID'])){ 
					$otp = $val['otp'];
					if($userdetails['otp']==$otp){ 
				
						$payload = array("status"=>'1',"usersid"=>$userdetails['ID'],"text"=>"OTP Verified");
					}else{
				
						$payload = array("status"=>'0',"usersid"=>"null","text"=>"Invalid OTP");
					}  
			}else{
					
				$payload = array("status"=>'0',"text"=>"Invalid user");
			}
		}else{
					
			$payload = array('status'=>'0','message'=>'Invalid Parameters');
		}
	return $payload;
	}
    public function updatepassword($val)
    {
        Yii::trace("===updatepassword val =====".json_encode($val));
		  if(!empty($val['usersid'])&&!empty($val['password'])){
		  $customer_id =  trim($val['usersid']);
		  $sqlrow = "SELECT * FROM users WHERE ID = '".$customer_id."'";
		  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
		  if(!empty($row['ID'])){
		$userarray = $userwherearray = array();
				$userwherearray['ID'] = $row['ID']; 
				$userarray['password'] = password_hash(trim($_REQUEST['password']), PASSWORD_DEFAULT);
				//$result = updateQuery($userarray,'users',$userwherearray);
				$sqlUpdate = 'update users set password = \''.$userarray['password'].'\' where ID = \''.$userwherearray['ID'].'\'';
				$result = Yii::$app->db->createCommand($sqlUpdate)->execute();
				if($result){
					
					$payload = array("status"=>'1',"text"=>"Password updated");
					}else{
					
					$payload = array("status"=>'0',"text"=>"Technical issue araised");
				}
	  }else{
					
			$payload = array("status"=>'0',"text"=>"Invalid users");
	  }
	  }else{
					
			$payload = array("status"=>'0',"text"=>"Invalid parameters");
	  }
	  return $payload;
	}
    public function changepassword($val)
	{
		if(!empty($val['usersid'])&&!empty($val['password'])&&!empty($val['oldpassword'])){
		  $oldpassword =  trim($val['oldpassword']);
		  $customer_id =  trim($val['usersid']);
		  $sqlrow = "SELECT * FROM users WHERE ID = '".$customer_id."'";
		  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
		  if(!empty($row['ID'])){
		  if(!empty($row['password'])&&!empty($oldpassword)&&password_verify($oldpassword,$row['password'])){
		$userarray = $userwherearray = array();
				$userwherearray['ID'] = $row['ID']; 
				$userarray['password'] = password_hash(trim($_REQUEST['password']), PASSWORD_DEFAULT);
				//$result = updateQuery($userarray,'users',$userwherearray);
				$sqlUpdate = 'update users set password = \''.$userarray['password'].'\' where ID = \''.$userwherearray['ID'].'\'';
				$result = Yii::$app->db->createCommand($sqlUpdate)->execute();
				if($result){
					
					$payload = array("status"=>'1',"text"=>"Password updated");
					}else{
					
					$payload = array("status"=>'0',"text"=>"Technical issue araised");
				}
	  }else{
					
			$payload = array("status"=>'0',"text"=>"Invalid Old password");
	  }
	  }else{
					
			$payload = array("status"=>'0',"text"=>"Invalid users");
	  }
	  }else{
					
			$payload = array("status"=>'0',"text"=>"Invalid parameters");
	  }
		return $payload;
	}
	public function addfeedback($val){
	    //Yii::debug('===qrcode parameters==='.json_encode($val));
		if(!empty($val['rating'])){ 
						$userarray = $userwherearray = array();
						$userarray['user_id'] = $val['header_user_id'];
						$userarray['order_id'] = trim($val['orderid']);
						$userarray['merchant_id'] = trim($val['merchantid']);
						$userarray['rating'] =  trim($val['rating']);
						$userarray['message'] =  !empty($val['message']) ? trim($val['message']) : ''; 
						$userarray['reg_date'] = date('Y-m-d h:i:s');
						//$result = insertQuery($userarray,'feedback');  
						$result = new \app\models\Feedback;
						$result->attributes = $userarray;
						if($result->save()){  
						$sqlUpdate = "update orders set orderprocess = '4',orderprocessstatus = '0' where ID = '".$userarray['order_id']."'";
						$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute(); 
							$payload = array("status"=>'1',"text"=>"Feedback updated");
						}else{ 	
							$payload = array("status"=>'1',"text"=>$result);
						}    
					}else{
									
						$payload = array('status'=>'0','message'=>'Invalid Parameters');
					}
					return $payload;
	}
	public function checkfeedback($val)
	{
		  if(!empty($val['header_user_id'])){ 
						$merchant_id = trim($val['merchantid']);
					  $sqlrow = "SELECT * FROM feedback WHERE user_id = '".$val['header_user_id']."' and merchant_id = '".$merchant_id."'";
					  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					  if(!empty($row['ID'])){
						  
							$payload = array("status"=>'1',"showfeedback"=>"false");
						  }  else {
						  
							$payload = array("status"=>'1',"showfeedback"=>"true");
						  }
					  }else{
							$payload = array("status"=>'0',"text"=>"Invalid users id");
					  }
					  return $payload;
	}
	public function setalert($val)
	{
		  if(!empty($val['header_user_id'])){ 
						$orderid = trim($val['orderid']);
					  $sqlrow = "SELECT * FROM orders WHERE user_id = '".$val['header_user_id']."' and ID = '".$orderid."'";
					  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					  if(!empty($row['ID'])){
							$sqlUpdate = "update orders set orderalert = '1' where ID = '".$row['ID']."'";
							$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
							  $sqlserviceboy = "SELECT * FROM serviceboy WHERE ID = '".$row['serviceboy_id']."'";
							  $serviceboy =  Yii::$app->db->createCommand($sqlserviceboy)->queryOne();
							if(!empty($serviceboy['push_id'])){
							$amessage = "Hey ".ucwords($serviceboy['name']).", Alert from ".Utility::table_details($row['tablename'],'name');	
							$title = 'Alert!!!!';
								$image = '';
							\app\helpers\Utility::sendPilotFCM($serviceboy['push_id'],$title,$amessage,$image,'7'); 
							}		
							$notificaitonarary = array();
							$notificaitonarary['merchant_id'] = $row['merchant_id'];
							$notificaitonarary['serviceboy_id'] = $row['serviceboy_id'];
							$notificaitonarary['order_id'] = $row['ID'];
							$notificaitonarary['title'] = 'Alert from Customer';
							$notificaitonarary['message'] = 'Alert from '.Utility::user_details($row['user_id'],'name')." with table ".Utility::table_details($row['tablename'],'name');
							$notificaitonarary['seen'] = '0';
							//insertQuery($notificaitonarary,'serviceboy_notifications');
							$model = new \app\models\ServiceboyNotifications;
							$model->attributes = $notificaitonarary;
							$model->reg_date = date('Y-m-d h:i:s');
							$model->reg_date = date('Y-m-d h:i:s');
							$model->ordertype = '0';
							$model->save();
							$payload = array("status"=>'1',"message"=>"Alert has sent to Restaurant team");
						  }  else { 
							$payload = array("status"=>'0',"message"=>"Invalid order details");
						  }
					  }else{
							$payload = array("status"=>'0',"text"=>"Invalid users id");
					  }
					  return $payload;
	}
	public function closefeedback($val)
	{
		  if(!empty($val['header_user_id'])){ 
						$orderid = trim($val['orderid']);
					  $sqlrow = "SELECT * FROM orders WHERE user_id = '".$val['header_user_id']."' and ID = '".$orderid."'";
					  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					  if(!empty($row['ID'])){
							$sqlupdate = "update orders set orderprocess = '4',orderprocessstatus = '0' where ID = '".$row['ID']."'";
							$resUpdate = Yii::$app->db->createCommand($sqlupdate)->execute();
							$payload = array("status"=>'1',"message"=>"Thank you");
						  }  else { 
							$payload = array("status"=>'0',"message"=>"Invalid order details");
						  }
					  }else{
							$payload = array("status"=>'0',"text"=>"Invalid users id");
					  }
		return $payload;
	}
	public function password($val){
		if(!empty($val['password'])){ 
							$userwherearray = $userarray = array();
							$userarray['password'] = password_hash(trim($val['password']),PASSWORD_DEFAULT);
							$userwherearray['ID'] = $val['header_user_id'];
								//$result = updateQuery($userarray,'users',$userwherearray);
								$sqlRes = 'update users set password = \''.$userarray['password'].'\' where ID = \''.$userwherearray['ID'].'\'';
								$result = Yii::$app->db->createCommand($sqlRes)->execute();
								if($result){ 
									
									$payload = array("status"=>'1',"text"=>"Password has been updated.");
								}else{
									
									$payload = array("status"=>'0',"text"=>$result);
								} 
							}else{
									
								$payload = array("status"=>'0',"text"=>"Please enter the password");
							}
							return $payload;
		
	}
	public function pushid($val)
	{
		if(!empty($val['pushid'])){
		$userdetails = \app\models\Users::find()->where(['ID'=>$val['header_user_id']])->asArray()->one();			
						$userwherearray = $userarray = array();
						$userarray['push_id'] =  $val['pushid'];
						$userwherearray['ID'] = $userdetails['ID'];
						//	$result = updateQuery($userarray,'users',$userwherearray);
							$sqlRes = 'update users set push_id = \''.$userarray['push_id'].'\' where ID = \''.$userwherearray['ID'].'\'';
								$result = Yii::$app->db->createCommand($sqlRes)->execute();
							if($result){ 
								
								$payload = array("status"=>'1',"text"=>"Push id has been updated.");
							}else{
								
								$payload = array("status"=>'0',"text"=>$result);
							} 
						}else{
								
							$payload = array("status"=>'0',"text"=>"Please enter the password");
						}
					return $payload;
	}
	public function updation($val)
	{
		if(!empty($val['name'])&&!empty($val['email'])&&!empty($val['mobile'])){ 
						$userarray = $userwherearray = array();
						$userwherearray['ID'] = $val['header_user_id'];
						$userarray['name'] = trim($val['name']);
						$userarray['email'] =  trim($val['email']);
						$userarray['mobile'] =  trim($val['mobile']);
						
						$sqlrow = "SELECT * FROM users WHERE email = '".$userarray['email']."' and ID <> '".$val['header_user_id']."'";
						$row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					if(empty($row['ID'])){
						$sqlrow = "SELECT * FROM users WHERE mobile = '".$userarray['mobile']."' and ID <> '".$val['header_user_id']."'";
						$row = Yii::$app->db->createCommand($sqlrow)->queryOne();
							if(empty($row['ID'])){  
							
						//	$result = updateQuery($userarray,'users',$userwherearray); 
						$sqlRes = 'update users set name = \''.$userarray['name'].'\',email=\''.$userarray['email'].'\',mobile=\''.$userarray['mobile'].'\' 
						where ID = \''.$userwherearray['ID'].'\'';
								$result = Yii::$app->db->createCommand($sqlRes)->execute();
		
								if($result){ 
											
									$payload = array("status"=>'1',"text"=>"Account has been updated");
								}else{
											
									$payload = array("status"=>'1',"text"=>$result);
								}  
						}else{
									
					$payload = array("status"=>'0',"text"=>"Mobile already exists please try another");
						}
					}else{
									
						$payload = array("status"=>'0',"text"=>"Email already exists please try another");
					}
					
					}else{
									
						$payload = array('status'=>'0','message'=>'Invalid Parameters');
					}
					return $payload;
	}
	public function users($usersid)
	{
			  if(!empty($usersid)){ 
					  $sqlrow = "SELECT * FROM users WHERE ID = '".$usersid."'";
					  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					  if(!empty($row['ID'])){
						  $customerdetails = array();
						  $customerdetails['id'] =  $row['ID'];
						  $customerdetails['name'] =  $row['name'];
						  $customerdetails['email'] =  $row['email'];
						  $customerdetails['mobile'] =  $row['mobile'];
						  $customerdetails['coins'] =  $row['coins'];
						  $customerdetails['referral_code'] =  $row['referral_code'];
						  $customerdetails['referral_content'] =  'Invite your friends & earn 25 rs each..!';
						  $customerdetails['meassage_content'] =  'Invite your friends to FOODq and 25 Rs Worth when they start ordering through app';
						  $customerdetails['profilepic'] =  \app\helpers\Utility::user_image($row['ID']);
						  $sqlsupportdata = "select * from superadmin where ID = '1'";
						  $supportdata =  Yii::$app->db->createCommand($sqlsupportdata)->queryOne();
						  $customerdetails['supportmobile'] =  $supportdata['supportmobile']; 
						  $customerdetails['supportemail'] =  $supportdata['supportemail']; 
						  $sqlcoinstransactionsarray = "select SUM(c.coins) as count,u.name,u.ID from coins_transactions c,users u where c.user_id = u.ID and c.type='Debit' group by c.user_id order by count desc limit 0,3";
						  $coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryAll();
						  
						  $coinsarray =  $coinstootarray = array();
						  if(count($coinstransactionsarray)>0){
						  foreach($coinstransactionsarray as $coinstransactions){
							   $coinsarray['coins'] = $coinstransactions['count'];
							   $coinsarray['name'] = $coinstransactions['name'];
							   $coinsarray['image'] = \app\helpers\Utility::user_image($coinstransactions['ID']);
							   $coinstootarray[] = $coinsarray;
						  }
						  }
						  $customerdetails['coinburners'] =  $coinstootarray;
						  
							$payload = array("status"=>'1',"users"=>$customerdetails);
						  }  else {
								
								$payload = array("status"=>'0',"text"=>"Invalid users");
						  }
					  }else{
							$payload = array("status"=>'0',"text"=>"Invalid users id");
					  }
					  return $payload;
	}
	public function notificationslist($val)
	{
		$date = date('Y-m-d');  
		$sqlorderlistarray = "select * from serviceboy_notifications where merchant_id = '".$serviceboydetails['merchant_id']."' 
		and serviceboy_id = '".$serviceboydetails['ID']."' and reg_date >= '".$date." 00:00:00' and reg_date <= '".$date." 23:59:59' 
		union select * from serviceboy_notifications where merchant_id = '".$serviceboydetails['merchant_id']."' 
		and reg_date >= '".$date." 00:00:00' and reg_date <= '".$date." 23:59:59' and ordertype = 'new' 
		and serviceboy_id = '0' order by ID desc";
		$orderlistarray = Yii::$app->db->createCommand($sqlorderlistarray)->queryAll();
		
		$sqlunseennotifications = "select ID from serviceboy_notifications where merchant_id = '".$serviceboydetails['merchant_id']."' 
		and serviceboy_id = '".$serviceboydetails['ID']."' and seen = '0' and reg_date >= '".$date." 00:00:00' 
		and reg_date <= '".$date." 23:59:59' union  select ID from serviceboy_notifications 
		where merchant_id = '".$serviceboydetails['merchant_id']."' and seen = '0' 
		and reg_date >= '".$date." 00:00:00' and reg_date <= '".$date." 23:59:59' and ordertype = 'new' and serviceboy_id = '0'";
		$unseennotifications = Yii::$app->db->createCommand($sqlunseennotifications)->queryAll();
					$unseennotifications = !empty($unseennotifications) ? count($unseennotifications) : 0;
					if(!empty($orderlistarray)){
					$orderarray = $totalordersarray = array();
					foreach($orderlistarray as $orderlist){
						$totalproductaarray = array();
						$orderarray['id'] =  $orderlist['ID'];
						$orderarray['order_id'] =  $orderlist['order_id'];
						$orderarray['title'] =  $orderlist['title']; 
						$orderarray['message'] =  $orderlist['message']; 
						$orderarray['seen'] =  $orderlist['seen'];  
						$orderarray['regdate'] =  date('d M Y H:i:s',strtotime($orderlist['reg_date'])); 
					$totalordersarray[] = $orderarray;
					}
				 	$payload = array('status'=>'1','count'=>$unseennotifications,'orders'=>$totalordersarray);  
					}else{
					$payload = array('status'=>'0','message'=>'Notification not found!!');
					}
					return $payload;
	}
	public function seenstatus($val){
		 if(!empty($serviceboydetails['ID'])&&!empty($serviceboydetails['merchant_id'])){
					$sqlUpdate = "update serviceboy_notifications set seen = '1' where 
					merchant_id = '".$serviceboydetails['merchant_id']."' and ordertype = 'new'";
					$result = Yii::$app->db->createCommand($sqlUpdate)->execute();
					$sqlUpdate  =	"update serviceboy_notifications set seen = '1' where serviceboy_id = '".$serviceboydetails['ID']."'"; 
					$result = Yii::$app->db->createCommand($sqlUpdate)->execute();					
					if($result){
						$payload = array('status'=>'1','message'=>'Data Updated');
						}
					 }else{
					$payload = array('status'=>'0','message'=>'Service boy not found.');
					 }
					 return $payload;
	}
	public function restaurants($val){
	    
	    if(!empty($val['latitude'])&&!empty($val['longitude'])){
					$latitude = $val['latitude']; 
					$longitude = $val['longitude']; 
					$sqlmerchantsarray = "SELECT * FROM  merchant where status = '1' and table_res_avail = '1'";
					if($val['food_serve_type'] == '2' || $val['food_serve_type'] == 2){
					    $sqlmerchantsarray .=" and food_serve_type=2";
					}
					$sqlmerchantsarray .= " ORDER BY ID DESC";
				
					$merchantsarray = Yii::$app->db->createCommand($sqlmerchantsarray)->queryAll();
					 if(!empty($merchantsarray)){ 
							$merchantlist = $merchants = array();
							foreach($merchantsarray as $merchantsdata){
							$merchants['id'] = $merchantsdata['ID'];
							$merchants['unique_id'] = $merchantsdata['unique_id'];
							//$merchants['name'] = $merchantsdata['name'];
							$merchants['name'] = $merchantsdata['storename'];
							$merchants['email'] = $merchantsdata['email'];
							$merchants['storename'] = $merchantsdata['storename'];
							$merchants['storetype'] = $merchantsdata['storetype'];
							$merchants['address'] = $merchantsdata['address'];
							$merchants['state'] = $merchantsdata['state'];
							$merchants['city'] = $merchantsdata['city'];
							$merchants['location'] = $merchantsdata['location'];
							$merchants['latitude'] =  $merchantsdata['latitude'];
							$merchants['longitude'] =  $merchantsdata['longitude'];
							$merchants['servingtype'] =  $merchantsdata['servingtype'];
							$merchants['verify'] =  $merchantsdata['verify'];
							$sqlfeedbackrating = "select avg(rating) as rating from feedback where merchant_id =  '".$merchantsdata['ID']."'";
							$feedbackrating = Yii::$app->db->createCommand($sqlfeedbackrating)->queryOne();
							$merchants['rating'] = !empty($feedbackrating) ? ceil($feedbackrating['rating']) : 0;
							$merchants['logo'] = !empty($merchantsdata['logo']) ?  MERCHANT_LOGO.$merchantsdata['logo'] : '';
							$merchants['coverpic'] = !empty($merchantsdata['coverpic']) ? MERCHANT_LOGO.$merchantsdata['coverpic'] : '';
							$merchants['food_serve_type'] = !empty($merchantsdata['food_serve_type']) ? $merchantsdata['food_serve_type'] : '';
							if($merchantsdata['food_serve_type'] == 2){
							    $sqltablename = 'select * from tablename where merchant_id=\''.$merchantsdata["ID"].'\' order by ID';
							     $tabledet = Yii::$app->db->createCommand($sqltablename)->queryOne();
							     $merchants['enckey'] = Utility::encrypt($merchantsdata['ID'].','.$tabledet['ID']);
							    
							}
								$merchantlist[] = $merchants;
							}
							$takeawayarr = [];
						
							Yii::trace("=====restaruant json=====".json_encode($merchantlist));
							$payload = array("status"=>'1',"merchantlist"=>$merchantlist) ;
					}else{ 
						$payload = array("status"=>'0',"text"=>"Invalid user");
					 }
					}else{ 
						$payload = array("status"=>'0',"text"=>"Invalid parameters");
					 }
					 return $payload;
	}
 	public function searchlocations($val)
	{
		$sqlmerchantsarray = "SELECT city,location,latitude,longitude FROM  merchant group by location ORDER BY ID DESC";
		$merchantsarray = Yii::$app->db->createCommand($sqlmerchantsarray)->queryAll();	
					 if(!empty($merchantsarray)){  
							$payload = array("status"=>'1',"locationlist"=>$merchantsarray);
					}else{ 
						$payload = array("status"=>'0',"text"=>"Invalid user");
					 }
		return $payload;
	}
	public function merchantbyid($val)
	{
		if(!empty($val['merchantid'])){
						$merchantid = $val['merchantid'];
					$sqlmerchantsdata = 'select * from merchant where status = \'1\' and ID = \''.$merchantid.'\'';
					$merchantsdata = Yii::$app->db->createCommand($sqlmerchantsdata)->queryOne();
					 if(!empty($merchantsdata)){
							$merchants['id'] = $merchantsdata['ID'];
							$merchants['unique_id'] = $merchantsdata['unique_id'];
							$merchants['name'] = $merchantsdata['name'];
							$merchants['email'] = $merchantsdata['email'];
							$merchants['storename'] = $merchantsdata['storename'];
							$merchants['storetype'] = $merchantsdata['storetype'];
							$merchants['address'] = $merchantsdata['address'];
							$merchants['state'] = $merchantsdata['state'];
							$merchants['city'] = $merchantsdata['city'];
							$merchants['location'] = $merchantsdata['location'];
							$merchants['latitude'] =  $merchantsdata['latitude'];
							$merchants['longitude'] =  $merchantsdata['longitude'];
							$merchants['servingtype'] =  $merchantsdata['servingtype'];
							$merchants['verify'] =  $merchantsdata['verify'];
							$merchants['logo'] = !empty($merchantsdata['logo']) ? MERCHANT_LOGO.$merchantsdata['logo'] : '';
							$merchants['coverpic'] = !empty($merchantsdata['coverpic']) ? MERCHANT_LOGO.$merchantsdata['coverpic'] : '';  
							
							$payload = array("status"=>'1',"merchant"=>$merchants);
					}else{
						
						$payload = array("status"=>'0',"text"=>"Invalid Merchant");
					 }
					}else{
						
						$payload = array("status"=>'0',"text"=>"Please select the merchant");
					 }
					 return $payload;
	}
	public function redeemcoins($val)
	{
		  if(!empty($val['header_user_id'])){
				$savingamt = 0; 
					$rewardid = trim($val['rewardid']);
					$avaicoins = (int)(\app\helpers\Utility::coins($val['header_user_id']));
					if(!empty($avaicoins)){
						$sqlrewards = "select * from rewards where ID = '".$rewardid."' and status = '1' order by ID desc";
						$rewards = Yii::$app->db->createCommand($sqlrewards)->queryOne();
						if(!empty($rewards)){ 
								if($rewards['status']=='1'){
									 $sqlcoinstransactionsarray = "SELECT * FROM coins_transactions WHERE user_id = '".$val['header_user_id']."' 
									 and reward_id = '".$rewards['ID']."'";
									 $coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryOne();
									 if(empty($coinstransactionsarray['ID'])){
								if(!empty($rewards['coins'])&&(int)$avaicoins>=$rewards['coins']){
									$todaydate = strtotime(date('Y-m-d H:i:s'));
									$datetime1 = strtotime($rewards['validityfrom']);
									$datetime2 = strtotime($rewards['validityto']);
									if($todaydate>=$datetime1){ 
										if($todaydate<=$datetime2){  
										$couponcode = $rewards['couponcode'];
										 $coinstransactions = array();
											$coinstransactions['user_id'] = $val['header_user_id'];
											$coinstransactions['txn_id'] = \app\helpers\Utility::coinstxn_id();
											$coinstransactions['reward_id'] = $rewards['ID'];
											$coinstransactions['coins'] = $rewards['coins'];
											$coinstransactions['type'] = 'Debit';
											$coinstransactions['reason'] = $rewards['coins']." coins debited from your wallet for the redemeption of copoun.";
											//$result = insertQuery($coinstransactions,"coins_transactions");
											$result = new \app\models\CoinsTransactions;
											$result->attributes = $coinstransactions;
											if($result->save()){
											\app\helpers\Utility::coins_deduct($val['header_user_id'],$rewards['coins']);
											}  
										$payload = array("status"=>"1","message"=>"Rewards  Applied","couponcode"=>$couponcode);
									}else{ 
										$sqlUPdate = "update rewards set status = '2' where ID = '".$rewards['ID']."'";
										$resupdate = Yii::$app->db->createCommand($sqlUPdate)->execute();
									 
									$payload = array("status"=>"0","message"=>"Rewards Validation expired","couponcode"=>""); 
									} 
									}else{   
									$payload = array('status'=>"0","message"=>"Rewards validation Pending","couponcode"=>""); 
									}
									}else{   
									$payload = array("status"=>"0","message"=>"Insufficient coins in your wallet.","couponcode"=>""); 
									} 
									}else{   
									$payload = array("status"=>"0","message"=>"Reward already reddemed.","couponcode"=>""); 
									} 
								}else{ 
								$payload = array("status"=>'0',"message"=>"Rewards Deactivated","couponcode"=>"");
								} 
						}else{ 
						$payload = array("status"=>'0',"message"=>"Invalid reward details","couponcode"=>"");

						} 
					}else{  
						$payload = array("status"=>'0',"message"=>"Coins is empty","couponcode"=>""); 
					} 
				  }else{ 
						$payload = array("status"=>'0',"message"=>"Invalid users","couponcode"=>"");
				  }
				  return $payload;
	}
	public function coinstransactionslist($val){
		 if(!empty($val['header_user_id'])){ 
					  $sqlcoinstransactionsarray = "SELECT * FROM coins_transactions WHERE user_id = '".$val['header_user_id']."' order by ID desc";
					  $coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryAll();
					  $coinsarray = $coinstotalarray =  array();
					  if(!empty($coinstransactionsarray)){ 
							foreach($coinstransactionsarray as $coinstransaction){ 
								 $coinsarray['id'] = $coinstransaction['ID'];
								  $coinsarray['merchant'] = \app\helpers\Utility::merchant_details($coinstransaction['merchant_id'],'name'); 
								  $coinsarray['orderid'] = \app\helpers\Utility::order_details($coinstransaction['order_id'],'order_id'); 
								  $coinsarray['coins'] = $coinstransaction['coins']; 
								  $coinsarray['type'] = $coinstransaction['type']; 
								  $coinsarray['regdate'] = $coinstransaction['reg_date'];
								  $coinsarray['reason'] = $coinstransaction['reason'];  
								  $coinsarray['rewardtitle'] ="";  
								  $coinsarray['rewardcoupon'] =""; 
								  								  $coinsarray['storename'] = \app\helpers\Utility::merchant_details($coinstransaction['merchant_id'],'storename');
								  if($coinstransaction['reward_id']>0){
									  $sqlrewardsde = "select * from rewards_coupons where ID = '".$coinstransaction['rewardcoupon_id']."'";
									  $rewardsde = Yii::$app->db->createCommand($sqlrewardsde)->queryOne();
									  if(!empty($rewardsde)){
										  $coinsarray['rewardtitle'] = \app\helpers\Utility::rewards_details($rewardsde['rewards_id'],'title');  
										  $coinsarray['rewardcoupon'] = $rewardsde['couponcode'];  
									  }
								  }
								   $coinstotalarray[] =  $coinsarray; 
							} 
							$payload = array("status"=>'1',"text"=>$coinstotalarray);
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Transactions not found");
					  } 
				  }else{
								
						$payload = array("status"=>'0',"text"=>"Invalid users");
				  }
		return $payload;
	}
	public function coinstransactions($val)
	{
		 if(!empty($val['header_user_id'])){ 
					  $sqlcoinstransactionsarray = "SELECT * FROM coins_transactions WHERE user_id = '".$val['header_user_id']."'";
					  $coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryAll();
					  $coinsarray = $coinstotalarray =  array();
					  if(!empty($coinstransactionsarray)){ 
							foreach($coinstransactionsarray as $coinstransaction){
								 $sqlrewards = "SELECT * FROM rewards WHERE status = '1' and ID = '".$coinstransaction['reward_id']."' order by ID desc";
								 $rewards = Yii::$app->db->createCommand($sqlrewards)->queryOne();
								 if(!empty($rewards)){
									$coinsarray['id'] = $rewards['ID'];
									$coinsarray['couponid'] = $coinstransaction['rewardcoupon_id'];
								  $coinsarray['title'] = $rewards['title']; 
								  $coinsarray['validityto'] = $rewards['validity'];  
								  if($rewards['validity']=='1'){
								   $validityto = strtotime($rewards['validityto']);
									$coinsarray['validityto'] = date('d M Y',$validityto);
								   $validitytoday = time();
								   if($validitytoday>$validityto){
									$coinsarray['validityto'] = '0';
										$sqlUpdate = "update rewards set validity = '0' where ID = '".$coinsarray['ID']."'";
										$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
								   }   
								  }
								  $coinsarray['reason'] = ''; 
								  $coinsarray['excerpt'] = $rewards['excerpt']; 
								  $coinsarray['logo'] = REWARDS_IMAGES.$rewards['logo']; 
								   $coinstotalarray[] =  $coinsarray;
								 }
							}
							
								$payload = array("status"=>'1',"text"=>$coinstotalarray);
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Transactions not found");
					  } 
				  }else{
								
						$payload = array("status"=>'0',"text"=>"Invalid users");
				  }
				  return $payload;
	}
	public function rewardscount($val)
	{
	if(!empty($val['header_user_id'])){ 
					  $sqlcoinstransactionsarray = "SELECT count(*) as count FROM rewards WHERE status = '1' order by ID desc";
					  $coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryOne();
							
								$payload = array("status"=>'1',"text"=>$coinstransactionsarray['count']);
					   
				  }else{
								
						$payload = array("status"=>'0',"text"=>"Invalid users");
				  }
		return $payload;				  
	}
	public function rewardslist()
	{
		$sqlcoinstransactionsarray = "SELECT * FROM rewards WHERE status = '1' order by ID desc";
		$coinstransactionsarray = Yii::$app->db->createCommand($sqlcoinstransactionsarray)->queryAll();
					  $coinsarray =  $coinstotalarray = array();
					  if(!empty($coinstransactionsarray)){
						  foreach($coinstransactionsarray as $rewards){
						  $coinsarray['id'] = $rewards['ID'];
						  $coinsarray['title'] = $rewards['title']; 
						  $coinsarray['validityto'] = $rewards['validity']; 
						  $coinsarray['soldout'] = $rewards['soldout']; 
						  if($rewards['validity']=='1'){
						  $validityto = strtotime($rewards['validityto']);
							$coinsarray['validityto'] = date('d M Y',$validityto);
						   $validitytoday = time();
						   if($validitytoday>$validityto){
							$coinsarray['validityto'] = '0';
								$sqlUpdate = "update rewards set validity = '0' where ID = '".$rewards['ID']."'";
								$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
						   } 
						   } 
						  $coinsarray['excerpt'] = $rewards['excerpt']; 
						  $coinsarray['logo'] = REWARDS_IMAGES.$rewards['logo']; 
						   $coinstotalarray[] =  $coinsarray;
						  } 
								$payload = array("status"=>'1',"text"=>$coinstotalarray);
					  }else{ 
								$payload = array("status"=>'0',"text"=>"Rewards not found");
					  }
					  return $payload;
	}
	public function rewardsdetails($val){
		$rewardid = $val['rewardid'];
				  if(!empty($rewardid)){
					  $sqlrewards = "SELECT * FROM rewards WHERE status = '1' and ID = '".$rewardid."' order by ID desc";
					  $rewards = Yii::$app->db->createCommand($sqlrewards)->queryOne();
					  $coinsarray =  $coinstotalarray = array();
					  if(!empty($rewards)){ 
						   $coinsarray['id'] = $rewards['ID'];
						  $coinsarray['title'] = $rewards['title'];
						  $coinsarray['coins'] = $rewards['coins'];
						  $validityfrom = strtotime($rewards['validityfrom']);
						  $coinsarray['validityfrom'] = date('d F Y',$validityfrom);
						  $validityto = strtotime($rewards['validityto']);
							$coinsarray['validityto'] = date('d F Y',$validityto);
						   $validitytoday = time();
						   if($validitytoday>$validityto){
							$coinsarray['validityto'] = '0';
							$sqlUpdate = "update rewards set validity = '0' where ID = '".$coinsarray['ID']."'";
							$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
						   }   
						  /* $date = time();
						  if($date<=$validityfrom){
							   $coinsarray['couponcode'] = ''; 
						  }else if($date>=$validityto){
							   $coinsarray['couponcode'] = 'Expired'; 
						  } */
						  $coinsarray['soldout'] = $rewards['soldout'];
						  $coinsarray['excerpt'] = $rewards['excerpt'];
						  $coinsarray['cover'] = REWARDS_IMAGES.$rewards['cover'];
						  $coinsarray['logo'] = REWARDS_IMAGES.$rewards['logo'];
						  $coinsarray['description'] = $rewards['description'];  
							
								$payload = array("status"=>'1',"text"=>$coinsarray);
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Rewards not found");
					  } 
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Reward id not found");
					  }
		return $payload;
	}
	public function redeemrewardsdetails($val){
		$rewardid = $val['rewardid'];
				  $rewardcouponid = $val['rewardcouponid'];
				  if(!empty($rewardid)&&!empty($rewardcouponid)){ 
					  $sqlrewardcoupons = "SELECT * FROM rewards_coupons WHERE  ID = '".$rewardcouponid."' and rewards_id = '".$rewardid."' and user_id = '".$val['header_user_id']."' order by ID desc";
					  $rewardcoupons = Yii::$app->db->createCommand($sqlrewardcoupons)->queryOne();
					  $sqlrewards = "SELECT * FROM rewards WHERE  ID = '".$rewardid."' order by ID desc";
					  $rewards = Yii::$app->db->createCommand($sqlrewards)->queryOne();
					  $coinsarray =  $coinstotalarray = array();
					  if(!empty($rewards)){ 
						   $coinsarray['id'] = $rewards['ID'];
						  $coinsarray['title'] = $rewards['title'];
						  $coinsarray['coins'] = $rewards['coins'];
						  $coinsarray['validityto'] = $rewards['validity'];
						  $coinsarray['couponcode'] = 'Expired';
						  if($rewards['validity']=='1'){ 
						  $validityfrom = strtotime($rewards['validityfrom']); 
						  $coinsarray['validityfrom'] = date('d F Y',$validityfrom);
							$validityto = strtotime($rewards['validityto']);
							$coinsarray['validityto'] = date('d M Y',$validityto);
						   $validitytoday = time();
						   if($validitytoday>$validityto){
							$coinsarray['validityto'] = '0';
							$sqlUpdate = "update rewards set validity = '0' where ID = '".$coinsarray['ID']."'";
							$resUPdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
						   }     
						  $coinsarray['couponcode'] = $rewardcoupons['couponcode']; 
						  $date = time();
						  if($date<=$validityfrom){
							   $coinsarray['couponcode'] = ''; 
						  }else if($date>=$validityto){
							   $coinsarray['couponcode'] = 'Expired'; 
						  }
						  
						  }
						  $coinsarray['excerpt'] = $rewards['excerpt'];
						  $coinsarray['cover'] = REWARDS_IMAGES.$rewards['cover'];
						  $coinsarray['logo'] = REWARDS_IMAGES.$rewards['logo'];
						  $coinsarray['description'] = $rewards['description'];
							
								$payload = array("status"=>'1',"text"=>$coinsarray);
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Rewards not found");
					  }
					  }else{
						  
								$payload = array("status"=>'0',"text"=>"Rewards not found");
					  } 
				  return $payload;
				  }
		
	
	public function applycoupon($val)
	{
	    Yii::debug("====apply coupon parameters====".json_encode($val));

		if(!empty($val['amount'])){
						$savingamt = 0; 
					$coupancode = trim($val['coupon']);
					$merchantdetails = trim($val['merchant_id']); 
						$sqlmerchant_details = 'select * from merchant where ID = \''.$merchantdetails.'\'';
						$resmerchant_details = Yii::$app->db->createCommand($sqlmerchant_details)->queryOne();
					$grandamt = $val['amount'];
					$subpercenttage = floor((1/100)*$grandamt);
					$taxpercenttage =  ($resmerchant_details['tax']/100)*$grandamt;
					if($subpercenttage<1){
						$subpercenttage = 0;
					}
					if($subpercenttage>10){
						$subpercenttage = 0;
					}
					$userarray = array();
							$customer_id = $val['header_user_id']; 
					  $sqlrow1 = "SELECT * FROM users WHERE ID = '".$customer_id."'";
					  $rowuser = Yii::$app->db->createCommand($sqlrow1)->queryOne();
					  
					  $userarray['name'] = $rowuser['name'];
					  $userarray['email'] = $rowuser['email'];
					  $userarray['mobilenumber'] = $rowuser['mobile'];
					  
				//	$tipsamount = 1.00;
				    $tipsamount = ($resmerchant_details['tip']/100)*$grandamt;
					$taxsamount = number_format($taxpercenttage, 2, '.', '');
				//	$subscriptionamount = number_format($subpercenttage, 2, '.', '');
					$subscriptionamount = null;
					$savingamt = 0;
					$userarray['status'] = 1;
					$userarray['amount'] = number_format($grandamt, 2, '.', '');
					$userarray['tax'] = $taxsamount;
					$userarray['tips'] = number_format($tipsamount, 2, '.', '');
					$userarray['subscription'] = $subscriptionamount;
					$userarray['coupanamt'] = 0.00;
					$compleamt = $grandamt+$taxsamount;
					$userarray['totalamt'] = number_format($compleamt, 2, '.', '');
					$userarray['savingamt'] = 0.00;
					$sqlpaymentarraytypes = "select paymenttype,paymentgateway,merchantid,merchantkey from merchant_paytypes where merchant_id = '".$merchantdetails."'";
					$paymentarraytypes = Yii::$app->db->createCommand($sqlpaymentarraytypes)->queryAll();
					$paypaymentarray =$totalpaymentarray = array();
					$paygatewayarray = array('0'=>'Cash On Delivery','1'=>'Paytm','2'=>'PhonePay','3'=>'online');
					foreach($paymentarraytypes as $paymentarray){
						$paypaymentarray['paymenttype'] = $paymentarray['paymenttype'];
						$paypaymentarray['paymentgateway'] = $paygatewayarray[$paymentarray['paymentgateway']];
						$paypaymentarray['merchantid'] = $paymentarray['merchantid'];
						$paypaymentarray['merchantkey'] = $paymentarray['merchantkey'];
						if($paypaymentarray['paymenttype']==1 && $paymentarray['paymentgateway']==1){
							$paypaymentarray['merchantid'] = 'CLgzWa00765150063711';
							$paypaymentarray['merchantkey'] = 'URQ5xLh_h0gDMDr&';
						}
						else if($paypaymentarray['paymenttype']==3 && $paymentarray['paymentgateway']==3){
							$paypaymentarray['merchantid'] = 'rzp_test_x7rPuSSUc7LN81';
							$paypaymentarray['merchantkey'] = 'rzp_test_x7rPuSSUc7LN81';
						}
						$totalpaymentarray[] = $paypaymentarray;
					}
					$userarray['paymentmethods'] = $totalpaymentarray;
						 $payprocess = true;  $paycalprocess = true; 
					if(!empty($coupancode)){
						$sqlcoupandetails = "select * from merchant_coupon where code LIKE '%".$coupancode."%'";
						$coupandetails = Yii::$app->db->createCommand($sqlcoupandetails)->queryOne();
						if($coupandetails){ 
								if($coupandetails['merchant_id']=='0'||$coupandetails['merchant_id']==$merchantdetails){
									if($coupandetails['status']=='Active'){ 
										$todaydate = strtotime(date('Y-m-d H:i:s')); 
										$datetime1 = strtotime($coupandetails['todate']); 
										if($todaydate<=$datetime1){ 
											if(empty($coupandetails['minorderamt'])||$grandamt>$coupandetails['minorderamt']){
													$couproductsarray = json_decode($val['productid']);
												if(!empty($coupandetails['product'])&&!empty($couproductsarray)){
													$productsarray = explode(',',$coupandetails['product']);
													$cminusamount = $minusamount = $totalamount =  $totalamount = 0;
													$applycou = array();
														foreach($couproductsarray as $couproduct){
															if(in_array($couproduct,$productsarray)){  
																$applycou[] = '1';
															}
														} 
														if(!empty($applycou)){
															if($coupandetails['type']=='percent'){ 
																$percentage = (int)$coupandetails['price']; 
																$minusamount = ($grandamt*$percentage)/100; 
																$totalamount = $grandamt-$minusamount; 
															}elseif($coupandetails['type']=='amount'){ 
																$minusamount = (int)$coupandetails['price']; 
																$totalamount = $grandamt-$minusamount; 
															}
															
															$maxamt = $coupandetails['maxamt'];
													if(!empty($maxamt)&&$minusamount>=$maxamt){ 
													$totalamount = $totalamount-$maxamt;
													$minusamount = $maxamt;
													}
														$savingamt = $savingamt+$minusamount;
													$userarray['coupanamt'] = number_format($minusamount, 2, '.', '');
													$compleamt = $totalamount+$taxsamount;
													$userarray['totalamt'] = number_format($compleamt, 2, '.', '');
													$userarray['savingamt'] = number_format($savingamt, 2, '.', '');
													
													 $payprocess = true;
													 
														}else{
															$payprocess = false;		
															$payload = array('status'=>'0',"message"=>"Coupon is not valid for this products."); 
														}  
													}else{ 
														if($coupandetails['type']=='percent'){ 
															$percentage = (int)$coupandetails['price']; 
															$minusamount = ($grandamt*$percentage)/100; 
															$totalamount = $grandamt-$minusamount; 
														}elseif($coupandetails['type']=='amount'){ 
															$minusamount = (int)$coupandetails['price']; 
															$totalamount = $grandamt-$minusamount; 
														} 
														
														$maxamt = $coupandetails['maxamt'];
													if(!empty($maxamt)&&$minusamount>=$maxamt){ 
													$totalamount = $totalamount-$maxamt;
													$minusamount = $maxamt;
													}
														$savingamt = $savingamt+$minusamount;
													$userarray['coupanamt'] = number_format($minusamount, 2, '.', '');
													$compleamt = $totalamount+$taxsamount;
													$userarray['totalamt'] = number_format($compleamt, 2, '.', '');
													$userarray['savingamt'] = number_format($savingamt, 2, '.', '');
													
													 $payprocess = true;
													 
													}
													

													}else{

													$payload = array('status'=>'0',"message"=>"Min Order value for applying coupon is ".$coupandetails['minorderamt']); 
															$payprocess = false;
													}
									}else{

										$sqlupdate = "update merchant_coupon set status = 'Deactive' where ID = '".$coupandetails['ID']."'";
										$resupdate = Yii::$app->db->createCommand($sqlupdate)->execute();

									$payload = array('status'=>"0","message"=>"Coupan code Validation expired"); 
															$payprocess = false;
									}
 
								}else{

								$payload = array('status'=>'0',"message"=>"Coupan code Deactivated"); 
															$payprocess = false;
								}

						}else{

						$payload = array('status'=>'0',"message"=>"Please enter a valid coupan code"); 
															$payprocess = false;
						}

						}else{

						$payload = array('status'=>'0',"message"=>"Please enter a valid coupan code"); 
															$payprocess = false;
						}  
						}
						}else{

						$payload = array('status'=>'0',"message"=>"Invalid amount calculation."); 
															$payprocess = false;
						}    
						if($payprocess){
						$payload = $userarray;	
						}
						return $payload;
	}
	public function cancelcoupon($val){
		if(!empty($val['amount'])){
					$savingamt = 0;  
					$merchantdetails = trim($val['merchant_id']); 
					$grandamt = $val['amount'];
					$subpercenttage = floor((1/100)*$grandamt);
					$taxpercenttage =  (2.5/100)*$grandamt;
					if($subpercenttage<1){
						$subpercenttage = 1;
					}
					if($subpercenttage>10){
						$subpercenttage = 10;
					}
					$userarray = array();
					$tipsamount = 1.00;
					$taxsamount = number_format($taxpercenttage, 2, '.', '');
					$subscriptionamount = $subpercenttage;
					$savingamount = 0;
					$userarray['status'] = 1;
					$userarray['amount'] = $grandamt;
					$userarray['tax'] = $taxsamount;
					$userarray['tips'] = $tipsamount;
					$userarray['subscription'] = $subscriptionamount;
					$userarray['coupanamt'] = 0;
					$userarray['totalamt'] = number_format($grandamt+$taxsamount, 2, '.', '');
					$userarray['savingamt'] = $savingamount;
					$userarray['coupanamt'] = 0;  
					$payload = $userarray;
				}
		return $payload;
	}
public function qrcodenew($val)
	{

	    		 Yii::debug('===qrcode parameters==='.json_encode($val));
		if(!empty($val['enckey'])){ 
						$userwherearray = $userarray = array();
						
						if ( strstr( $val['enckey'], 'foodqonline' ) ) {
                            $pos = explode('?',$val['enckey']);
                            parse_str($pos[1], $outputencarray);
                            $enckey = \app\helpers\Utility::decrypt($outputencarray['enckey']);
                        } else {
						    $enckey = \app\helpers\Utility::decrypt(trim($val['enckey']));
                        }
						

    					 $foodtype = trim($val['foodtype']) ?: 0;
						 $latfrom = trim($val['lat']) ?: 0;
						 $lngfrom = trim($val['lng']) ?: 0;
						/* $enckey = trim($_POST['enckey']);*/ 
							if(!empty($enckey)){
							    if($latfrom == 0 || $lngfrom == 0){
							        $payload = array("status"=>'0',"text"=>"Unable to get your location");
							        exit;
							    }
								$merchantexplode = explode(',',$enckey);
								$merchantid = $merchantexplode[0];
								$tableid = $merchantexplode[1];
								
								$tabel_Det = \app\models\Tablename::findOne($tableid);
								
								if($tabel_Det['current_order_id'] != 0 || $tabel_Det['current_order_id'] != null )
								{
								    $currentOrder = \app\models\Orders::findOne($tabel_Det['current_order_id']);
								    if($currentOrder['user_id'] != $val['header_user_id'] ){
								        $payload = array("status"=>'0',"text"=>"Table is already occupied");
									    return $payload;
									    exit;
								    }    
								}
								
								$sqlmerchantdetails = 'select * from merchant where ID =  \''.$merchantid.'\' and status = \'1\'';
								$merchantdetails = Yii::$app->db->createCommand($sqlmerchantdetails)->queryOne();
						
							if(!empty($merchantdetails)){
									$latlngdistance = \app\helpers\Utility::haversineGreatCircleDistance($latfrom,$lngfrom,$merchantdetails['latitude'],$merchantdetails['longitude']);
						
									if($latlngdistance > $merchantdetails['scan_range']){
									    $payload = array("status"=>'0',"text"=>"Restaurent or  theater distance is too long");
									//return $payload;
									 //   exit;
									}
									$sqltabledetails = 'select * from tablename where merchant_id = \''.$merchantdetails['ID'].'\' and ID = \''.$tableid.'\' and status = \'1\'';
									$tabledetails = Yii::$app->db->createCommand($sqltabledetails)->queryOne();
									
									if(!empty($tabledetails)){
									    
									$sqlSections = 'select s.ID section_id,s.section_name from sections s 
									inner join tablename tn on s.ID = tn.section_id
									where tn.merchant_id = \''.$merchantdetails['ID'].'\' and s.ID = \''.$tabledetails['section_id'].'\'';
									$resSections = Yii::$app->db->createCommand($sqlSections)->queryAll();

									if(!empty($resSections)){
		$sqlproductDetails = 'select P.ID,fs.ID food_section_id,fs.food_section_name,P.title,P.food_category_quantity,P.price,P.image
		,fc.food_category,fc.ID  food_category_ID
		,case when food_type_name is not null then concat(title ,\' (\' , food_type_name , \')\') else title end  title_quantity  
		from product P 
		left join food_categeries fc on fc.ID = P.foodtype  
		left join food_sections fs on fc.food_section_id =  fs.ID
		left join food_category_types fct on fct.ID =  P.food_category_quantity 
		and fct.merchant_id =  \''.$tabledetails['merchant_id'].'\'
		where P.merchant_id = \''.$tabledetails['merchant_id'].'\'';
		$productDetails = Yii::$app->db->createCommand($sqlproductDetails)->queryAll();
		$foodSectionArr =  array_values(array_unique(array_filter(array_column($productDetails,'food_section_id'))));
		$fsNameArr = array_column($productDetails,'food_section_name','food_section_id');
		$titleNameArr = array_column($productDetails,'title_quantity','ID');
		
		$sqlfoodCategories = 'select * from food_categeries where merchant_id = \''.$tabledetails['merchant_id'].'\'';
		$foodCategories = Yii::$app->db->createCommand($sqlfoodCategories)->queryAll();
		
		$sqlProducts = 'select * from product where merchant_id = \''.$tabledetails['merchant_id'].'\'';
		$resproducts = Yii::$app->db->createCommand($sqlProducts)->queryAll();
		
		$productsIndexArr = \yii\helpers\ArrayHelper::index($resproducts, 'ID');
		

		
		$fcArr = array_column($foodCategories,'food_category','ID'); 
		

		for($i=0;$i<count($productDetails);$i++)
		{
				$fsCatArr[$productDetails[$i]['food_section_id']][$i] = $productDetails[$i]['food_category_ID'];
		        $fcProdArr[$productDetails[$i]['food_category_ID']][$i] = $productDetails[$i]['ID'];
		}
	
	//return print_r($fcProdArr);
$getproducts = array();
		if(!empty($foodSectionArr)){
		    for($fs = 0;$fs <count($foodSectionArr);$fs++){
		        
		        $fcId = array_values(array_unique($fsCatArr[$foodSectionArr[$fs]]));
		         
		        for($fc =0; $fc < count($fcId) ; $fc++){
		            $foodCatArr[$fc]['id'] = $fcId[$fc];
		            $foodCatArr[$fc]['name'] = $fcArr[$fcId[$fc]];
		            //echo $fcId[$fc]."<br>";
		            $prodIDArr = array_values($fcProdArr[$fcId[$fc]]);
		            //print_r($prodIDArr); 
		            $prodArr = array();
					for($fp=0;$fp<count($prodIDArr);$fp++){
					$sqlSectionPrice = 'select * from section_item_price_list 
					        where merchant_id= \''.$merchantdetails['ID'].'\' and item_id = \''.$prodIDArr[$fp].'\' 
					        and section_id = \''.$tabledetails['section_id'].'\'';
					        $resSectionPrice = Yii::$app->db->createCommand($sqlSectionPrice)->queryOne();					
						
						
		                $prodArr[$fp]['id'] = $prodIDArr[$fp]; 
		                $prodArr[$fp]['unique_id'] = $productsIndexArr[$prodIDArr[$fp]]['unique_id']; 
		                $prodArr[$fp]['title'] = $productsIndexArr[$prodIDArr[$fp]]['title'];
						$prodArr[$fp]['labeltag'] = $productsIndexArr[$prodIDArr[$fp]]['labeltag'];
						$prodArr[$fp]['serveline'] = $productsIndexArr[$prodIDArr[$fp]]['serveline'];
						$prodArr[$fp]['price'] = !empty($resSectionPrice) ? $resSectionPrice['section_item_price'] : $productsIndexArr[$prodIDArr[$fp]]['price'];
						$prodArr[$fp]['food_category'] = \app\helpers\Utility::foodtype_value_another($productsIndexArr[$prodIDArr[$fp]]['foodtype'],$merchantdetails['ID']);
						$prodArr[$fp]['saleprice'] = !empty($resSectionPrice) ? $resSectionPrice['section_item_sale_price'] : $productsIndexArr[$prodIDArr[$fp]]['saleprice'];
						$prodArr[$fp]['availabilty'] = $productsIndexArr[$prodIDArr[$fp]]['availabilty']; 
						$prodArr[$fp]['image'] = !empty($productsIndexArr[$prodIDArr[$fp]]['image']) ? MERCHANT_PRODUCT_URL.$productsIndexArr[$prodIDArr[$fp]]['image'] : '';
						$prodArr[$fp]['title_quantity'] = $titleNameArr[$prodIDArr[$fp]];
						
		            }
		            $foodCatArr[$fc]['products']  = $prodArr; 
					unset($prodArr);
				}
				
		        
		        $getproducts[$fs]['id'] = $foodSectionArr[$fs];
		        $getproducts[$fs]['name'] = $fsNameArr[$foodSectionArr[$fs]];
		        $getproducts[$fs]['subcategories'] = $foodCatArr;
		        
		        
		        
		    }
			
		   	
		    
		}	
									    
							/*			if($foodtype>0){
										$sqlmerchantproductsarray = 'select * from product where merchant_id = \''.$merchantdetails['ID'].'\' and  status = \'1\' and foodtype = \''.$foodtype.'\'';
										}else{
											$sqlmerchantproductsarray = 'select * from product where merchant_id = \''.$merchantdetails['ID'].'\' and  status = \'1\'';
										}
										$merchantproductsarray = Yii::$app->db->createCommand($sqlmerchantproductsarray)->queryAll();
										$getproducts = array();
										foreach($merchantproductsarray as $merchantproduct){
										$singleproducts = array();
										$singleproducts['id'] = $merchantproduct['ID'];
										$singleproducts['unique_id'] = $merchantproduct['unique_id'];
										$singleproducts['title'] = $merchantproduct['title'];
										$singleproducts['labeltag'] = $merchantproduct['labeltag'];
										$singleproducts['serveline'] = $merchantproduct['serveline'];
										$singleproducts['price'] = $merchantproduct['price'];
										$singleproducts['food_category'] = \app\helpers\Utility::foodtype_value_another($merchantproduct['foodtype'],$merchantdetails['ID']);
										$singleproducts['saleprice'] = $merchantproduct['saleprice'];
										$singleproducts['availabilty'] = $merchantproduct['availabilty']; 
										$singleproducts['image'] = !empty($merchantproduct['image']) ? MERCHANT_PRODUCT_URL.$merchantproduct['image'] : '';
										$getproducts[] = $singleproducts;
										} */ 
										$merchantlgo = !empty($merchantdetails['logo']) ? MERCHANT_LOGO.$merchantdetails['logo'] : '';
                                    
										$merchantcoverpic = !empty($merchantdetails['coverpic']) ? MERCHANT_LOGO.$merchantdetails['coverpic'] : '';
										
										    $sqlcategoryDetail = 'select 0 foodtype, \'Recommended\' food_category ,count(foodtype) itemcount  from product where merchant_id = \''.$merchantid.'\'
                                                        
                                                        union all
select foodtype,case when foodtype = \'0\' then \'All\'  else fc.food_category end as food_category
                                                        ,count(foodtype) itemcount  from product p
                                                        left join food_categeries fc on fc.id = p.foodtype
                                                        where p.merchant_id = \''.$merchantid.'\'
                                                        group by foodtype';
											$categoryDetail = Yii::$app->db->createCommand($sqlcategoryDetail)->queryAll();
							            
										$payload = array("status"=>'1',"merchantid"=>$merchantdetails['ID'],"table"=>$tabledetails['ID']
										,"tablename"=>$tabledetails['name'],"store"=>$merchantdetails['storename'],"storetype"=>$merchantdetails['storetype']
										,"servingtype"=>$merchantdetails['servingtype'],"verify"=>$merchantdetails['verify']
										,"location"=>$merchantdetails['location'],"logo"=>$merchantlgo,"coverpic"=>$merchantcoverpic
										,"categories"=>$getproducts,'categoryDetail'=>$categoryDetail);
    									}else{
    									    $payload = array("status"=>'0',"text"=>"Requires atleast one section");
    									}
									    
									}else{
										
										$payload = array("status"=>'0',"text"=>"Invalid Table or seat details scan again");
									}
								}else{
									
									$payload = array("status"=>'0',"text"=>"Invalid Restaurent or  theater can again");
								}
							}else{
									
								$payload = array("status"=>'0',"text"=>"Please scan again");
							}
						}else{
								
							$payload = array("status"=>'0',"text"=>"Please scan again");
						}
		return $payload;
	}
	public function qrcode($val)
	{
 		 Yii::debug('===qrcodenew parameters==='.json_encode($val));
		if(!empty($val['enckey'])){ 
						$userwherearray = $userarray = array();
						
						if ( strstr( $val['enckey'], 'foodqonline' ) ) {
                            $pos = explode('?',$val['enckey']);
                            parse_str($pos[1], $outputencarray);
                            $enckey = \app\helpers\Utility::decrypt($outputencarray['enckey']);
                        } else {
						    $enckey = \app\helpers\Utility::decrypt(trim($val['enckey']));
                        }
						

    					 $foodtype = trim($val['foodtype']) ?: 0;
						 $latfrom = trim($val['lat']) ?: 0;
						 $lngfrom = trim($val['lng']) ?: 0;
						/* $enckey = trim($_POST['enckey']);*/ 
							if(!empty($enckey)){
							    if($latfrom == 0 || $lngfrom == 0){
							        $payload = array("status"=>'0',"text"=>"Unable to get your location");
							        exit;
							    }
								$merchantexplode = explode(',',$enckey);
								$merchantid = $merchantexplode[0];
								$tableid = $merchantexplode[1];
								
								$tabel_Det = \app\models\Tablename::findOne($tableid);
								
								if($tabel_Det['current_order_id'] != 0 || $tabel_Det['current_order_id'] != null )
								{
								    $currentOrder = \app\models\Orders::findOne($tabel_Det['current_order_id']);
								    if($currentOrder['user_id'] != $val['header_user_id'] ){
								        $payload = array("status"=>'0',"text"=>"Table is already occupied");
									    return $payload;
									    exit;
								    }    
								}
								
								$sqlmerchantdetails = 'select * from merchant where ID =  \''.$merchantid.'\' and status = \'1\'';
								$merchantdetails = Yii::$app->db->createCommand($sqlmerchantdetails)->queryOne();
						
							if(!empty($merchantdetails)){
									$latlngdistance = \app\helpers\Utility::haversineGreatCircleDistance($latfrom,$lngfrom,$merchantdetails['latitude'],$merchantdetails['longitude']);
						
									if($latlngdistance > $merchantdetails['scan_range']){
									    $payload = array("status"=>'0',"text"=>"Restaurent or  theater distance is too long");
									//return $payload;
									 //   exit;
									}
									$sqltabledetails = 'select * from tablename where merchant_id = \''.$merchantdetails['ID'].'\' and ID = \''.$tableid.'\' and status = \'1\'';
									$tabledetails = Yii::$app->db->createCommand($sqltabledetails)->queryOne();
									if(!empty($tabledetails)){
										if($foodtype>0){
										$sqlmerchantproductsarray = 'select * from product where merchant_id = \''.$merchantdetails['ID'].'\' and  status = \'1\' and foodtype = \''.$foodtype.'\'';
										}else{
											$sqlmerchantproductsarray = 'select * from product where merchant_id = \''.$merchantdetails['ID'].'\' and  status = \'1\'';
										}
										$merchantproductsarray = Yii::$app->db->createCommand($sqlmerchantproductsarray)->queryAll();
										$getproducts = array();
										foreach($merchantproductsarray as $merchantproduct){
										$singleproducts = array();
										$singleproducts['id'] = $merchantproduct['ID'];
										$singleproducts['unique_id'] = $merchantproduct['unique_id'];
										$singleproducts['title'] = $merchantproduct['title'];
										$singleproducts['labeltag'] = $merchantproduct['labeltag'];
										$singleproducts['serveline'] = $merchantproduct['serveline'];
										$singleproducts['price'] = $merchantproduct['price'];
										$singleproducts['food_category'] = \app\helpers\Utility::foodtype_value_another($merchantproduct['foodtype'],$merchantdetails['ID']);
										$singleproducts['saleprice'] = $merchantproduct['saleprice'];
										$singleproducts['availabilty'] = $merchantproduct['availabilty']; 
										$singleproducts['image'] = !empty($merchantproduct['image']) ? MERCHANT_PRODUCT_URL.$merchantproduct['image'] : '';
										$getproducts[] = $singleproducts;
										} 
										$merchantlgo = !empty($merchantdetails['logo']) ? MERCHANT_LOGO.$merchantdetails['logo'] : '';
                                    
										$merchantcoverpic = !empty($merchantdetails['coverpic']) ? MERCHANT_LOGO.$merchantdetails['coverpic'] : '';
										
										    $sqlcategoryDetail = 'select 0 foodtype, \'Recommended\' food_category ,count(foodtype) itemcount  from product where merchant_id = \''.$merchantid.'\'
                                                        
                                                        union all
select foodtype,case when foodtype = \'0\' then \'All\'  else fc.food_category end as food_category
                                                        ,count(foodtype) itemcount  from product p
                                                        left join food_categeries fc on fc.id = p.foodtype
                                                        where p.merchant_id = \''.$merchantid.'\'
                                                        group by foodtype';
											$categoryDetail = Yii::$app->db->createCommand($sqlcategoryDetail)->queryAll();
											$getproductsreindex = \yii\helpers\ArrayHelper::index($getproducts, null, 'title');
											$newProduclistArr = [];
											$pr = 0;
											foreach($getproductsreindex as $catName => $catItems){
												$newProduclistArr[$pr]['categoryName'] =$catName;
												$newProduclistArr[$pr]['items'] =$catItems;
												$pr++;
											}
										$payload = array("status"=>'1',"merchantid"=>$merchantdetails['ID'],"table"=>$tabledetails['ID']
										,"tablename"=>$tabledetails['name'],"store"=>$merchantdetails['storename'],"storetype"=>$merchantdetails['storetype']
										,"servingtype"=>$merchantdetails['servingtype'],"verify"=>$merchantdetails['verify']
										,"location"=>$merchantdetails['location'],"logo"=>$merchantlgo,"coverpic"=>$merchantcoverpic
										,"productlist"=>$newProduclistArr,'categoryDetail'=>$categoryDetail);
									}else{
										
										$payload = array("status"=>'0',"text"=>"Invalid Table or seat details scan again");
									}
								}else{
									
									$payload = array("status"=>'0',"text"=>"Invalid Restaurent or  theater can again");
								}
							}else{
									
								$payload = array("status"=>'0',"text"=>"Please scan again");
							}
						}else{
								
							$payload = array("status"=>'0',"text"=>"Please scan again");
						}
		return $payload;	
	    
	}
	public function qrusers($val)
	{
		$customer_id = $val['header_user_id']; 
					  $sqlrow = "SELECT * FROM users WHERE ID = '".$customer_id."'";
					  $row = Yii::$app->db->createCommand($sqlrow)->queryOne();
					  if(!empty($row['ID'])){
						  $customerdetails =  $row;
						  
							$payload = array("status"=>'1',"customer"=>$customerdetails);
						  }  else {
								
								$payload = array("status"=>'0',"text"=>"Invalid users");
						  }
		return $payload;
	}
	public function cash($val){
	    	    		 Yii::debug('===cash parameters==='.json_encode($val));
				$sqlusers = 'select * from users where ID = \''.$val['header_user_id'].'\'';
		$userdetails = Yii::$app->db->createCommand($sqlusers)->queryOne();
		if(!empty($val['merchantid'])&&!empty($val['table'])&&!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){
		    
		    $tabel_Det = \app\models\Tablename::findOne($val['table']);
		if(!empty($tabel_Det)){
								if($tabel_Det['current_order_id'] != 0 || $tabel_Det['current_order_id'] != null)
								{
								        $payload = array("status"=>'0',"text"=>"Table is already occupied");
									    return $payload;
									    exit;
								}
		}
						$userwherearray = $userarray = array();
						$couponcode = !empty($val['coupon']) ? trim($val['coupon']) : '';
						$merchantid = trim($val['merchantid']);
						$table = trim($val['table']);
						$valtax = !empty($val['tax']) ? number_format(trim($val['tax']), 2, '.', ',') : 0;
							$userarray['user_id'] = $val['header_user_id']; 
							$userarray['merchant_id'] = $merchantid;
							$userarray['serviceboy_id'] = isset($val['serviceboy_id']) ? $val['serviceboy_id'] : '';
							$userarray['tablename'] = 	$table; 
							$userarray['order_id'] = \app\helpers\Utility::order_id($merchantid,'order'); 
							$userarray['txn_id'] = \app\helpers\Utility::order_id($merchantid,'transaction'); 
							$userarray['txn_date'] = date('Y-m-d H:i:s');
							$productprice = !empty($val['price']) ? array_sum(array_filter(json_decode($val['price']))) : 0;
							$userarray['amount'] = $productprice ? number_format($productprice, 2, '.', ',') : 0;
							$userarray['tax'] = (string)$valtax;
							$userarray['tips'] = !empty($val['tips']) ? number_format(trim($val['tips']), 2, '.', ',') : '0';
							$userarray['subscription'] = !empty($val['subscription']) ?  number_format(trim($val['subscription']), 2, '.', ',') : '0';
							$userarray['totalamount'] =  !empty($val['totalamount']) ?  number_format(trim($val['totalamount']), 2, '.', ',') : 0;
							$userarray['couponamount'] = !empty($val['couponamount']) ? number_format(trim($val['couponamount']), 2, '.', ',') : 0;
							$userarray['paymenttype'] = 'cash';
							$userarray['orderprocess'] = '0';
							$userarray['status'] = '1';
							$userarray['paidstatus'] = '0';
							$userarray['paymentby'] = '1';
							$userarray['ordertype'] = 1;
							$userarray['coupon'] = $couponcode;
							$sqlprevmerchnat = "select max(orderline) as id from orders where merchant_id = '".$merchantid."' and reg_date >='".date('Y-m-d')." 00:00:01' and reg_date <='".date('Y-m-d')." 23:59:59'";
							$resprevmerchnat = Yii::$app->db->createCommand($sqlprevmerchnat)->queryOne();
							$prevmerchnat = $resprevmerchnat['id']; 
							 
							$newid = $prevmerchnat>0 ? $prevmerchnat+1 : 100;  
							$userarray['orderline'] = (string)$newid;
							$result = new \app\models\Orders;
							$result->attributes = $userarray;
							$result->reg_date = date('Y-m-d h:i:s');
							$result->couponamount = (string)$userarray['couponamount'];
		
							//$result = insertQuery($userarray,"orders");
							if($result->save()){
								
									$sqlorderdetails = 'select * from orders where ID = \''.$result->ID.'\'';
									$orderdetails = Yii::$app->db->createCommand($sqlorderdetails)->queryOne();
									
							$transactionscount = array(); 
							$transactionscount['order_id'] = $orderdetails['ID'];
							$transactionscount['user_id'] = $userdetails['ID'];
							$transactionscount['merchant_id'] = $merchantid;
							$transactionscount['amount'] = !empty($userarray['amount']) ? number_format(trim($userarray['amount']),2, '.', ',') : 0; 
							$transactionscount['couponamount'] =  !empty($userarray['couponamount']) ? number_format(trim($userarray['couponamount']),2, '.', ',') : 0; 
							$transactionscount['tax'] =  !empty($userarray['tax']) ? number_format(trim($userarray['tax']),2, '.', ',') : 0; 
							$transactionscount['tips'] =  !empty($userarray['tips']) ? number_format(trim($userarray['tips']),2, '.', ',') : '0'; 
							$transactionscount['subscription'] =  !empty($userarray['subscription']) ? number_format(trim($userarray['subscription']),2, '.', ',') : '0'; 
							$transactionscount['totalamount'] =   !empty($userarray['totalamount']) ? number_format(trim($userarray['totalamount']),2, '.', ',') : 0; 
							$transactionscount['paymenttype'] = 'cash';
							$transactionscount['reorder'] = '0';
							$transactionscount['paidstatus'] = '0';
							//$result = insertQuery($transactionscount,"order_transactions");
							$ordertransmodel = new \app\models\OrderTransactions;
							$ordertransmodel->attributes = $transactionscount;
							$ordertransmodel->couponamount = (string)$transactionscount['couponamount'];
							$ordertransmodel->reg_date = date('Y-m-d h:i:s');
							$ordertransmodel->save();

								if(!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){
									$productidsarray = json_decode($val['productid']);
									$productcountarray = json_decode($val['count']);
									$productpricearray = json_decode($val['price']);
									$x=1;
									for($i=0;$i<count($productidsarray);$i++){
										$productscount = array();
										$productscount['order_id'] = $orderdetails['ID'];
										$productscount['user_id'] = $userdetails['ID'];
										$productscount['merchant_id'] = $merchantid;
										$productscount['product_id'] = trim($productidsarray[$i]);
										$productscount['count'] = trim($productcountarray[$i]);
										$productscount['price'] = trim($productpricearray[$i]);
										$productscount['inc'] = (string)$x;
										$productscount['reorder'] = '0';
										//$result = insertQuery($productscount,"order_products");
										$orderProdModel = new \app\models\OrderProducts;
										$orderProdModel->attributes = $productscount;
										$orderProdModel->reg_date = date('Y-m-d h:i:s');
										$orderProdModel->save();
										

									$x++; }

											$title = 'Order has been placed!!';
											$message = "Hi ".$userdetails['name'].", Your order has been placed. ".$newid." is your line Please Wait for your turn Thank you.";
											$image = '';
										 Yii::$app->merchant->send_sms($userdetails['mobile'],$message); 
										if(!empty($userdetails['push_id'])){
										\app\helpers\Utility::sendFCM($userdetails['push_id'],$title,$message,$image,null,null,$orderdetails['ID']);
										}
										if(!empty($couponcode)){
										$sqlcoupandetails = "select * from merchant_coupon where code LIKE '".$couponcode."'";
										$coupandetails = Yii::$app->db->createCommand($sqlserviceboyarray)->queryOne();
											if(!empty($coupandetails)&&$coupandetails['purpose']=='Single'){
											$sqlUpdate = "update merchant_coupon set status = 'Deactive' where ID = '".$coupandetails['ID']."'";
											$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
											}
											}
											$sqlserviceboyarray = "select * from serviceboy where merchant_id = '".$merchantid."' and loginstatus = '1' and push_id <> '' order by ID desc";
											$serviceboyarray = Yii::$app->db->createCommand($sqlserviceboyarray)->queryAll();
											if(!empty($serviceboyarray)){
												$stitle = 'New order.';
												$smessage = 'New order received please check the app for information.';
												$simage = '';
												foreach($serviceboyarray as $serviceboy){
												    Yii::trace("======order distibution to service boy=======".$serviceboy['name']."========email========".$serviceboy['email']."=========pushid=======".$serviceboy['push_id']);
													\app\helpers\Utility::sendPilotFCM($serviceboy['push_id'],$stitle,$smessage,$simage,'6',null,$orderdetails['ID']); 
												}
											}
											$notificaitonarary = array();
											$notificaitonarary['merchant_id'] = $merchantid;
											$notificaitonarary['serviceboy_id'] = '0';
											$notificaitonarary['order_id'] = $orderdetails['ID'];
											$notificaitonarary['title'] = 'New Order';
											$notificaitonarary['message'] = 'New Order request from '.$userdetails['name']." with order id ".$orderdetails['order_id'];
											$notificaitonarary['ordertype'] = 'new';
											$notificaitonarary['seen'] = '0';
											//$result = insertQuery($notificaitonarary,'serviceboy_notifications'); 
											$serviceBoyNotiModel = new  \app\models\ServiceboyNotifications;
											$serviceBoyNotiModel->attributes = $notificaitonarary;
											$serviceBoyNotiModel->reg_date = date('Y-m-d h:i:s');
											$serviceBoyNotiModel->mod_date = date('Y-m-d h:i:s');
											$serviceBoyNotiModel->save();
											    Yii::debug('===cash table==='.json_encode($table));
											$tableUpdate = \app\models\Tablename::findOne($table);
						                    $tableUpdate->table_status = '1';
						                    $tableUpdate->current_order_id = $orderdetails['ID'];
						                    if($tableUpdate->validate()){
						                     $tableUpdate->save();   
						                    }
						                    else{
						                       Yii::debug('===cash errors==='.json_encode($tableUpdate->getErrors()));
						                    }
										$payload = array("status"=>'1',"id"=>$orderdetails['ID'],"text"=>"Order Created successfully");
									
								}
							}else{
							    print_r($result->getErrors());
								$payload = array("status"=>'0',"text"=>"Order Failed Please order again");
							}
							
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid parameters");
						}
		return $payload;
	}
	public function reordercash($val){
	    	    		 Yii::debug('===reordercash parameters==='.json_encode($val));
		$sqlusers = 'select * from users where ID = \''.$val['header_user_id'].'\'';
		$userdetails = Yii::$app->db->createCommand($sqlusers)->queryOne();
		if(!empty($val['merchantid'])&&!empty($val['table'])&&!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){ 
						$userwherearray = $userarray = array();
						if(!empty($val['orderid'])){
						$orderid = !empty($val['orderid']) ? trim($val['orderid']) : ''; 
						 //$orderdetails = selectQuery("orders","ID = '".$orderid."'");
							$sqlorderdetails = 'select * from orders where ID = \''.$orderid.'\'';
							$orderdetails = Yii::$app->db->createCommand($sqlorderdetails)->queryOne();
						$orderamount = trim($val['totalamount']);
							$totalamount = number_format($orderdetails['totalamount']+$orderamount, 2, '.', ',');
							$productprice = !empty($val['price']) ? array_sum(array_filter(json_decode($val['price']))) : 0;
							$amountant = $productprice ? $productprice : 0;
							$userarray['amount'] =  number_format($orderdetails['amount']+$amountant, 2, '.', ',');
							$couponamount = !empty($val['couponamount']) ? trim($val['couponamount']) : 0;
							$userarray['couponamount'] = number_format($orderdetails['couponamount']+$couponamount, 2, '.', ',');
							$taxamount = !empty($val['tax']) ? trim($val['tax']) : 0;
							$userarray['tax'] = number_format($orderdetails['tax']+$taxamount, 2, '.', ',');
							$tipsamount = !empty($val['tips']) ? trim($val['tips']) : 0;
							$userarray['tips'] = number_format($orderdetails['tips']+$tipsamount, 2, '.', ',');
							$subscriptionamount = !empty($val['subscription']) ? trim($val['subscription']) : '0';
							$userarray['subscription'] = number_format($orderdetails['subscription']+$subscriptionamount, 2, '.', ','); 
							$merchantid = trim($val['merchantid']); 
							$userarray['reorderprocess'] = '1'; 
							$userarray['orderprocess'] = '1'; 
							$userarray['paidstatus'] = '0';
							$userarray['paymentby'] = '1';
							$userarray['totalamount'] = $totalamount; 
							$userwwherearray['ID'] = $orderid; 
							//$result = updateQuery($userarray,"orders",$userwwherearray);
							$result = \app\models\Orders::findOne($orderid);
							$result->attributes = $userarray;
							if($result->save()){
									$sqlcoinsdata = "select * from coins_transactions where user_id = '".$orderdetails['user_id']."' and order_id = '".$orderdetails['order_id']."'";
									$coinsdata = Yii::$app->db->createCommand($sqlcoinsdata)->queryOne();
									if(!empty($coinsdata['user_id'])&&!empty($coinsdata['coins'])){
									$sqlDelete = "delete from coins_transactions where ID = '".$coinsdata['ID']."'";
									$resDelete = Yii::$app->db->createCommand($sqlDelete)->execute();
									\app\helpers\Utility::coins_deduct($coinsdata['user_id'],$coinsdata['coins']);
									}
								 $transactionscount = array(); 
								$transactionscount['order_id'] = $orderdetails['ID'];
								$transactionscount['user_id'] = $orderdetails['user_id'];
								$transactionscount['merchant_id'] = $merchantid;
								$transactionscount['amount'] = !empty($amountant) ? number_format(trim($amountant),2, '.', ',') : 0; 
								$transactionscount['couponamount'] =  !empty($couponamount) ? number_format(trim($couponamount),2, '.', ',') : 0; 
								$transactionscount['tax'] =  !empty($taxamount) ? number_format(trim($taxamount),2, '.', ',') : 0;
								$transactionscount['tips'] =  !empty($tipsamount) ? number_format(trim($tipsamount),2, '.', ',') : 0; 
								$transactionscount['subscription'] =  !empty($subscriptionamount) ? number_format(trim($subscriptionamount),2, '.', ',') : 0;
								$transactionscount['totalamount'] =  !empty($orderamount) ? number_format(trim($orderamount),2, '.', ',') : 0;
								$transactionscount['paymenttype'] = 'cash';
								$transactionscount['reorder'] = '1';
								$transactionscount['paidstatus'] = '0';
								//$result = insertQuery($transactionscount,"order_transactions");
								$orderTransModel = new \app\models\OrderTransactions;
								$orderTransModel->attributes = $transactionscount;
								$orderTransModel->couponamount = (string)$transactionscount['couponamount'];
								$orderTransModel->reg_date = date('Y-m-d h:i:s');
								$orderTransModel->save();

								if(!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){
									$productidsarray = json_decode($val['productid']);
									$productcountarray = json_decode($val['count']);
									$productpricearray = json_decode($val['price']);
									$sqltopincdata = "select max(inc) as inc from order_products where order_id = '".$orderdetails['ID']."'";
									$topincdata = Yii::$app->db->createCommand($sqltopincdata)->queryOne();
									$newid = (int)$topincdata['inc'];
									$x=$newid+1;
									for($i=0;$i<count($productidsarray);$i++){
										$productscount = array();
										$productscount['order_id'] = $orderdetails['ID'];
										$productscount['user_id'] = $userdetails['ID'];
										$productscount['merchant_id'] = $merchantid;
										$productscount['product_id'] = trim($productidsarray[$i]);
										$productscount['count'] = trim($productcountarray[$i]);
										$productscount['price'] = trim($productpricearray[$i]);
										$productscount['reorder'] = '1';
										$productscount['inc'] = (string)$x;
										
										//$result = insertQuery($productscount,"order_products");
										$orderProdModel = new \app\models\OrderProducts;
										$orderProdModel->attributes = $productscount;
										$orderProdModel->reg_date = date('Y-m-d h:i:s');
										$orderProdModel->save();
									$x++; }
	
											$title = 'Order Items has been added!!';
											$message = "Hi ".$userdetails['name'].", Your order has been placed. ".$orderdetails['order_id']." is your line Please Wait for your turn Thank you.";
											$image = '';
											Yii::$app->merchant->send_sms($userdetails['mobile'],$message);
											if(!empty($userdetails['push_id'])){
												\app\helpers\Utility::sendFCM($userdetails['push_id'],$title,$message,$image,null,null,$orderdetails['ID']);
											} 
											$servicepushid = \app\helpers\Utility::serviceboy_details($orderdetails['serviceboy_id'],'push_id');
											if(!empty($servicepushid)){
												$stitle = 'Order items have been added.';
												$smessage = 'Order received please check the app for information.';
												$simage = ''; 
													\app\helpers\Utility::sendPilotFCM($servicepushid,$stitle,$smessage,$simage,'6',null,$orderdetails['ID']); 
											}
											$notificaitonarary = array();
											$notificaitonarary['merchant_id'] = $merchantid;
											$notificaitonarary['serviceboy_id'] = $orderdetails['serviceboy_id'];
											$notificaitonarary['order_id'] = $orderdetails['ID'];
											$notificaitonarary['title'] = 'Reorder Request';
											$notificaitonarary['message'] = 'Reorder Order request from '.$userdetails['name']." with order id ".$orderdetails['order_id'];
											$notificaitonarary['seen'] = '0';
											$notificaitonarary['ordertype'] = 'reorder';
											//insertQuery($notificaitonarary,'serviceboy_notifications');
											$serviceBoyNotiModel = new  \app\models\ServiceboyNotifications;
											$serviceBoyNotiModel->attributes = $notificaitonarary;
											$serviceBoyNotiModel->reg_date = date('Y-m-d h:i:s');
											$serviceBoyNotiModel->save();
										$payload = array("status"=>'1',"id"=>$orderdetails['ID'],"text"=>"Order Updated successfully");
									
								}
							}else{
								
								$payload = array("status"=>'0',"text"=>"Order Failed Please order again");
							}
							
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid order");
						}
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid parameters");
						}
		
		return $payload;
	}
	public function prepaid($val)
	{
		$sqlusers = 'select * from users where ID = \''.$val['header_user_id'].'\'';
		$userdetails = Yii::$app->db->createCommand($sqlusers)->queryOne();
		if(!empty($val['merchantid'])&&!empty($val['table'])&&!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){ 
						$userwherearray = $userarray = array();
						$couponcode = !empty($val['coupon']) ? trim($val['coupon']) : '';
						$merchantid = trim($val['merchantid']);
						$table = trim($val['table']);
							$userarray['user_id'] = $val['header_user_id']; 
							$userarray['merchant_id'] = $merchantid;
							$userarray['tablename'] = 	$table;
							$userarray['order_id'] = \app\helpers\Utility::order_id($merchantid,'order'); 
							$userarray['txn_id'] =  trim($val['transactionid']);
							$userarray['txn_date'] =  trim($val['transactiondate']); 
							$productprice = !empty($val['price']) ? array_sum(array_filter(json_decode($val['price']))) : 0;
							$userarray['amount'] = $productprice ? number_format($productprice, 2, '.', ',') : 0;
							$userarray['tax'] = !empty($val['tax']) ? number_format(trim($val['tax']), 2, '.', ',') : 0;
							$userarray['tips'] = !empty($val['tips']) ? number_format(trim($val['tips']), 2, '.', ',') : '0';
							$userarray['subscription'] = !empty($val['subscription']) ?  number_format(trim($val['subscription']), 2, '.', ',') : '0';
							$userarray['totalamount'] =  !empty($val['totalamount']) ?  number_format(trim($val['totalamount']), 2, '.', ',') : 0;
							$userarray['couponamount'] = !empty($val['couponamount']) ? number_format(trim($val['couponamount']), 2, '.', ',') : 0;
							$userarray['paymenttype'] = 'paytm';
							$userarray['orderprocess'] = '0';
							$userarray['status'] = '1';
							$userarray['paidstatus'] = '1';
							$userarray['paymentby'] = !empty($val['paymenttype']) ? trim($val['paymenttype']) : '1';
							$userarray['coupon'] = $couponcode;
						//	$prevmerchnat = runQuery("select max(orderline) as id from orders where merchant_id = '".$merchantid."' and reg_date >='".date('Y-m-d')." 00:00:01' and reg_date <='".date('Y-m-d')." 23:59:59'")['id']; 
							$sqlprevmerchnat = "select max(orderline) as id from orders where merchant_id = '".$merchantid."' and reg_date >='".date('Y-m-d')." 00:00:01' and reg_date <='".date('Y-m-d')." 23:59:59'";
							$resprevmerchnat = Yii::$app->db->createCommand($sqlprevmerchnat)->queryOne();
							$prevmerchnat = $resprevmerchnat['id']; 						

							$newid = $prevmerchnat>0 ? $prevmerchnat+1 : 100;  
							$userarray['orderline'] = (string)$newid;
							//$result = insertQuery($userarray,"orders");
							$result = new \app\models\Orders;
							$result->attributes = $userarray;
							$result->reg_date = date('Y-m-d h:i:s');
							$result->couponamount = (string)$userarray['couponamount'];
							
							if($result->save()){
							//		$orderdetails = selectQuery("orders","order_id = '".$userarray['order_id']."'");
							$sqlorderdetails = 'select * from orders where ID = \''.$result->ID.'\'';
							$orderdetails = Yii::$app->db->createCommand($sqlorderdetails)->queryOne();

							$transactionscount = array(); 
							$transactionscount['order_id'] = $orderdetails['ID'];
							$transactionscount['user_id'] = $val['header_user_id'];
							$transactionscount['merchant_id'] = $merchantid;
						$transactionscount['amount'] = !empty($userarray['amount']) ? number_format(trim($userarray['amount']),2, '.', ',') : 0; 
							$transactionscount['couponamount'] =  !empty($userarray['couponamount']) ? number_format(trim($userarray['couponamount']),2, '.', ',') : 0; 
							$transactionscount['tax'] =  !empty($userarray['tax']) ? number_format(trim($userarray['tax']),2, '.', ',') : 0; 
							$transactionscount['tips'] =  !empty($userarray['tips']) ? number_format(trim($userarray['tips']),2, '.', ',') : 0; 
							$transactionscount['subscription'] =  !empty($userarray['subscription']) ? number_format(trim($userarray['subscription']),2, '.', ',') : 0; 
							$transactionscount['totalamount'] =   !empty($userarray['totalamount']) ? number_format(trim($userarray['totalamount']),2, '.', ',') : 0; 
							$transactionscount['paymenttype'] = 'paytm';
							$transactionscount['reorder'] = '0';
							$transactionscount['paidstatus'] = '1';
							//$result = insertQuery($transactionscount,"order_transactions");
							$ordertransmodel = new \app\models\OrderTransactions;
							$ordertransmodel->attributes = $transactionscount;
							$ordertransmodel->couponamount = (string)$transactionscount['couponamount'];
							$ordertransmodel->reg_date = date('Y-m-d h:i:s');
							$ordertransmodel->save();
								if(!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){
									$productidsarray = json_decode($val['productid']);
									$productcountarray = json_decode($val['count']);
									$productpricearray = json_decode($val['price']);
									$x=1; 
									for($i=0;$i<count($productidsarray);$i++){
										$productscount = array();
										$productscount['order_id'] = $orderdetails['ID'];
										$productscount['user_id'] = $val['header_user_id'];
										$productscount['merchant_id'] = $merchantid;
										$productscount['product_id'] = trim($productidsarray[$i]);
										$productscount['count'] = trim($productcountarray[$i]);
										$productscount['price'] = trim($productpricearray[$i]);
										$productscount['reorder'] = '0';
										$productscount['inc'] = (string)$x;
										//$result = insertQuery($productscount,"order_products");
										$orderProdModel = new \app\models\OrderProducts;
										$orderProdModel->attributes = $productscount;
										$orderProdModel->reg_date = date('Y-m-d h:i:s');
										$orderProdModel->save();
									$x++; }
										$title = 'Order has been placed!!';
										$message = "Hi ".$userdetails['name'].", Your order has been placed. ".$newid." is your line Please Wait for your turn Thank you.";
										Yii::$app->merchant->send_sms($userdetails['mobile'],$message);
										
										if(!empty($userdetails['push_id'])){
										\app\helpers\Utility::sendFCM($userdetails['push_id'],$title,$message,null,null,$orderdetails['ID']);
										}
										if(!empty($couponcode)){
											$sqlcoupandetails = "select * from merchant_coupon where code LIKE '".$couponcode."'";
											$coupandetails = Yii::$app->db->createCommand($ssqlcoupandetails)->queryOne();
											if(!empty($coupandetails)&&$coupandetails['purpose']=='Single'){
											$sqlUpdate = "update merchant_coupon set status = 'Deactive' where ID = '".$coupandetails['ID']."'";
											$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute(); 
											}
											}
												$sqlserviceboyarray = "select * from serviceboy where merchant_id = '".$merchantid."' and loginstatus = '1' and push_id <> '' order by ID desc";
												$serviceboyarray = Yii::$app->db->createCommand($sqlserviceboyarray)->queryAll();
											if(!empty($serviceboyarray)){
												$stitle = 'New order.';
												$smessage = 'New order received please check the app for information.';
												$simage = '';
												 foreach($serviceboyarray as $serviceboy){ 
													\app\helpers\Utility::sendPilotFCM($serviceboy['push_id'],$stitle,$smessage,$simage,'6',null,$orderdetails['ID']); 
												}
											}
											$notificaitonarary = array();
											$notificaitonarary['merchant_id'] = $merchantid;
											$notificaitonarary['serviceboy_id'] = 0;
											$notificaitonarary['order_id'] = $orderdetails['ID'];
											$notificaitonarary['title'] = 'New Order';
											$notificaitonarary['message'] = 'New Order request from '.$userdetails['name']." with order id ".$orderdetails['order_id'];
											$notificaitonarary['ordertype'] = 'new';
											$notificaitonarary['seen'] = '0';
											//$result = insertQuery($notificaitonarary,'serviceboy_notifications'); 
											$serviceBoyNotiModel = new  \app\models\ServiceboyNotifications;
											$serviceBoyNotiModel->attributes = $notificaitonarary;
											$serviceBoyNotiModel->reg_date = date('Y-m-d h:i:s');
											$serviceBoyNotiModel->save();
										$payload = array("status"=>'1',"id"=>$orderdetails['ID'],"text"=>"Order Created successfully");
									
								}
							}else{
								
								$payload = array("status"=>'0',"text"=>"Order Failed Please order again");
							}
							
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid parameters");
						}
						return $payload;
	}
	public function reorderprepaid($val)
	{
			$sqlusers = 'select * from users where ID = \''.$val['header_user_id'].'\'';
		$userdetails = Yii::$app->db->createCommand($sqlusers)->queryOne();
				if(!empty($val['merchantid'])&&!empty($val['table'])&&!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){ 
						$userwherearray = $userarray = array();
						if(!empty($val['orderid'])){
						$orderid = !empty($val['orderid']) ? trim($val['orderid']) : ''; 
						 //$orderdetails = selectQuery("orders","ID = '".$orderid."'");
						$sqlorderdetails = 'select * from orders where ID = \''.$orderid.'\'';
						$orderdetails = Yii::$app->db->createCommand($sqlorderdetails)->queryOne();
						$orderamount = trim($val['totalamount']);
							$totalamount = number_format($orderdetails['totalamount']+$orderamount, 2, '.', ',');
							$productprice = !empty($val['price']) ? array_sum(array_filter(json_decode($val['price']))) : 0;
							$amountant = $productprice ? $productprice : 0;
							$userarray['amount'] =  number_format($orderdetails['amount']+$amountant, 2, '.', ',');
							$couponamount = !empty($val['couponamount']) ? trim($val['couponamount']) : 0;
							$userarray['couponamount'] = number_format($orderdetails['couponamount']+$couponamount, 2, '.', ',');
							$taxamount = !empty($val['tax']) ? trim($val['tax']) : 0;
							$userarray['tax'] = number_format($orderdetails['tax']+$taxamount, 2, '.', ',');
							$tipsamount = !empty($val['tips']) ? trim($val['tips']) : 0;
							$userarray['tips'] = number_format($orderdetails['tips']+$tipsamount, 2, '.', ',');
							$subscriptionamount = !empty($val['subscription']) ? trim($val['subscription']) : 0;
							$userarray['subscription'] = number_format($orderdetails['subscription']+$subscriptionamount, 2, '.', ','); 
							$merchantid = trim($val['merchantid']); 
							$userarray['reorderprocess'] = '1'; 
							$userarray['orderprocess'] = '1'; 
							$userarray['paidstatus'] = '1';
							$userarray['paymentby'] = !empty($val['paymenttype']) ? trim($val['paymenttype']) : '1';
							$userarray['totalamount'] = $totalamount; 
							$userwwherearray['ID'] = $orderid; 
							//$result = updateQuery($userarray,"orders",$userwwherearray);
							$result = \app\models\Orders::findOne($orderid);
							$result->attributes = $userarray;
							if($result->save()){
									$sqlcoinsdata = "select * from coins_transactions where user_id = '".$orderdetails['user_id']."' and order_id = '".$orderdetails['order_id']."'";
									$coinsdata = Yii::$app->db->createCommand($sqlcoinsdata)->queryOne();
									if(!empty($coinsdata['user_id'])&&!empty($coinsdata['coins'])){
									$sqlDelete = "delete from coins_transactions where ID = '".$coinsdata['ID']."'";
									$resDelete = Yii::$app->db->createCommand($sqlDelete)->execute();
									\app\helpers\Utility::coins_deduct($coinsdata['user_id'],$coinsdata['coins']);
									}
										 $transactionscount = array(); 
								$transactionscount['order_id'] = $orderdetails['ID'];
								$transactionscount['user_id'] = $orderdetails['user_id'];
								$transactionscount['merchant_id'] = $merchantid;
									$transactionscount['amount'] = !empty($amountant) ? number_format(trim($amountant),2, '.', ',') : 0; 
								$transactionscount['couponamount'] =  !empty($couponamount) ? number_format(trim($couponamount),2, '.', ',') : 0; 
								$transactionscount['tax'] =  !empty($taxamount) ? number_format(trim($taxamount),2, '.', ',') : 0;
								$transactionscount['tips'] =  !empty($tipsamount) ? number_format(trim($tipsamount),2, '.', ',') : 0; 
								$transactionscount['subscription'] =  !empty($subscriptionamount) ? number_format(trim($subscriptionamount),2, '.', ',') : 0;
								$transactionscount['totalamount'] =  !empty($orderamount) ? number_format(trim($orderamount),2, '.', ',') : 0;
								$transactionscount['paymenttype'] = 'cash';
								$transactionscount['reorder'] = '1'; 
								$transactionscount['paidstatus'] = '1';
								//$result = insertQuery($transactionscount,"order_transactions");
								$ordertransmodel = new \app\models\OrderTransactions;
							$ordertransmodel->attributes = $transactionscount;
							$ordertransmodel->couponamount = (string)$transactionscount['couponamount'];
							$ordertransmodel->reg_date = date('Y-m-d h:i:s');
							$ordertransmodel->save();
								if(!empty($val['productid'])&&!empty($val['count'])&&!empty($val['price'])){
									$productidsarray = json_decode($val['productid']);
									$productcountarray = json_decode($val['count']);
									$productpricearray = json_decode($val['price']);
									$sqltopincdata = "select max(inc) as inc from order_products where order_id = '".$orderdetails['ID']."'";
									$topincdata = Yii::$app->db->createCommand($sqltopincdata)->queryOne();
									$newid = (int)$topincdata['inc'];
									$x=$newid+1;
									for($i=0;$i<count($productidsarray);$i++){
										$productscount = array();
										$productscount['order_id'] = $orderdetails['ID'];
										$productscount['user_id'] = $userdetails['ID'];
										$productscount['merchant_id'] = $merchantid;
										$productscount['product_id'] = trim($productidsarray[$i]);
										$productscount['count'] = trim($productcountarray[$i]);
										$productscount['price'] = trim($productpricearray[$i]);
										$productscount['reorder'] = '1';
										$productscount['inc'] = (string)$x;
									//	$result = insertQuery($productscount,"order_products");
									$orderProdModel = new \app\models\OrderProducts;
										$orderProdModel->attributes = $productscount;
										$orderProdModel->reg_date = date('Y-m-d h:i:s');
										$orderProdModel->mod_date = date('Y-m-d h:i:s');
										$orderProdModel->save();
									$x++; }
									
											$title = 'Order Items has been added!!';
											$message = "Hi ".$userdetails['name'].", Your order has been placed. ".$orderdetails['order_id']." is your line Please Wait for your turn Thank you.";
											$image = '';
											Yii::$app->merchant->send_sms($userdetails['mobile'],$message);
											if(!empty($userdetails['push_id'])){
												\app\helpers\Utility::sendFCM($userdetails['push_id'],$title,$message,$image,null,null,$orderdetails['ID']);
											} 
											$servicepushid = \app\helpers\Utility::serviceboy_details($orderdetails['serviceboy_id'],'push_id');
											if(!empty($servicepushid)){
												$stitle = 'Order items have been added.';
												$smessage = 'Order received please check the app for information.';
												$simage = ''; 
													\app\helpers\Utility::sendPilotFCM($servicepushid,$stitle,$smessage,$simage,'6',null,$orderdetails['ID']); 
											}
											$notificaitonarary = array();
											$notificaitonarary['merchant_id'] = $merchantid;
											$notificaitonarary['serviceboy_id'] = $orderdetails['serviceboy_id'];
											$notificaitonarary['order_id'] = $orderdetails['ID'];
											$notificaitonarary['title'] = 'Reorder Request';
											$notificaitonarary['message'] = 'Reorder Order request from '.$userdetails['name']." with order id ".$orderdetails['order_id'];
											$notificaitonarary['seen'] = '0';
											$notificaitonarary['ordertype'] = 'reorder';
											//insertQuery($notificaitonarary,'serviceboy_notifications');
											$serviceBoyNotiModel = new  \app\models\ServiceboyNotifications;
											$serviceBoyNotiModel->attributes = $notificaitonarary;
											$serviceBoyNotiModel->reg_date = date('Y-m-d h:i:s');
											$serviceBoyNotiModel->save();
										$payload = array("status"=>'1',"id"=>$orderdetails['ID'],"text"=>"Order Updated successfully");
									
								}
							}else{
								
								$payload = array("status"=>'0',"text"=>"Order Failed Please order again");
							}
							
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid order");
						}
						}else{
								
							$payload = array("status"=>'0',"text"=>"Invalid parameters");
						}
		return $payload;
	}
	public function orderlist($val){
	    	    Yii::debug('===orderlist parameters==='.json_encode($val));
		$sqlorderlistarray = "select * from orders where user_id = '".$val['header_user_id']."' and status = '1' order by ID desc";
		$orderlistarray = Yii::$app->db->createCommand($sqlorderlistarray)->queryAll();
					if(!empty($orderlistarray)){
					$orderarray = $totalordersarray = array();
					foreach($orderlistarray as $orderlist){
						$sqlmerchantdetails = "select * from merchant where ID = '".$orderlist['merchant_id']."'";
						$merchantdetails = Yii::$app->db->createCommand($sqlmerchantdetails)->queryOne();
						
							$sqlfeedbackrating = "select  rating from feedback where merchant_id =  '".$orderlist['merchant_id']."' and order_id = '".$orderlist['ID']."' and user_id = '".$val['header_user_id']."'";
							$feedbackrating = Yii::$app->db->createCommand($sqlfeedbackrating)->queryOne();
						$totalproductaarray = array();
						$orderarray['order_id'] =  $orderlist['ID'];
						$orderarray['unique_id'] =  $orderlist['order_id'];
						$orderarray['merchant_id'] =  $orderlist['merchant_id']; 
						$orderarray['logo'] = !empty($merchantdetails['logo']) ? MERCHANT_LOGO.$merchantdetails['logo'] : '';
						$orderarray['coverpic'] = !empty($merchantdetails['coverpic']) ? MERCHANT_LOGO.$merchantdetails['coverpic'] : ''; 
						$orderarray['storename'] = $merchantdetails['storename'];
						$orderarray['verify'] = $merchantdetails['verify'];
						$orderarray['serviceboy'] = \app\helpers\Utility::serviceboy_details($orderlist['serviceboy_id'],"name");
						$orderarray['tablename'] = \app\helpers\Utility::table_details($orderlist['tablename'],"name"); 
						$orderarray['amount'] =  $orderlist['amount'];
						$orderarray['couponamount'] =  $orderlist['couponamount'];
						$orderarray['tax'] =  $orderlist['tax'];
						$orderarray['tips'] =  $orderlist['tips'];
						$orderarray['subscription'] =  $orderlist['subscription'];
						$orderarray['totalamount'] =  $orderlist['totalamount'];  
						$orderarray['preparetime'] =  $orderlist['preparetime'];  
						$minutes = $orderlist['preparetime'];
						if(!empty($orderlist['preparetime']) && $orderlist['preparetime'] > '0' && $orderlist['orderprocess'] == '1' && !empty($orderlist['preparedate'])){
							$datetime1 = time();
							$datetime2 = strtotime($orderlist['preparedate']);
							$interval = $datetime1 - $datetime2;   
							$remainingsec = $orderlist['preparetime']*60 - $interval;
							$orderarray['preparetime'] =  $remainingsec;
							if($remainingsec<=0){
							$sqlUPdate = "update orders set preparetime = '0' where ID = '".$orderlist['ID']."'";
							$resUPdate = Yii::$app->db->createCommand($sqlUPdate)->execute(); 
							}
						}
						$orderarray['paymenttype'] =  $orderlist['paymenttype']=='cash' ? 'Cash' : 'Online';
						$orderarray['orderprocess'] =  $orderlist['orderprocess'];
						$orderarray['orderprocesstext'] =  \app\helpers\Utility::orderstatus_details($orderlist['orderprocess']);
						$orderarray['orderprocessstatus'] =  $orderlist['orderprocessstatus'];
						$orderarray['rating'] = !empty($feedbackrating) ? ceil($feedbackrating['rating']) : 0;
						$orderarray['orderdate'] =  date('d M Y',strtotime($orderlist['reg_date']));
						$orderarray['enckey'] =  \app\helpers\Utility::encrypt($orderlist['merchant_id'].','.$orderlist['tablename']); 
						/* code for alert disapper in app */
						if($orderlist['orderprocessstatus']=='1'){
							$currentdate = time();
							$deliveerdate = strtotime('+4 hours', strtotime($orderlist['deliverdate'])); 
						if($currentdate>$deliveerdate){
							 $sqlUpdate = "update orders set orderprocess = '4',orderprocessstatus = '0' where ID = '".$orderlist['ID']."'";
							 $resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute(); 
							$orderarray['orderprocessstatus'] =  '0';
							$orderarray['orderprocess'] =  '4'; 
							}
						}
						$orderarray['paidstatus'] =  $orderlist['paidstatus']=='1' ? 'Paid' : 'Unpaid';
						$orderarray['feedbackstatus'] =  'false'; 
						$orderarray['showaddmore'] = '1';
						//$feedbackstatus = selectQuery("feedback","order_id = '".$orderlist['ID']."' and merchant_id = '".$orderlist['merchant_id']."' and user_id = '".$userdetails['ID']."'");
						$sqlfeedbackstatus = 'select * from feedback where order_id = \''.$orderlist['ID'].'\' and merchant_id = \''.$orderlist['merchant_id'].'\' and user_id = \''.$val['header_user_id'].'\'';
						$feedbackstatus = Yii::$app->db->createCommand($sqlfeedbackstatus)->queryAll();
						if(!empty($feedbackstatus)){
						$orderarray['feedbackstatus'] =  'true';
						$orderarray['showaddmore'] = '0';
						$orderarray['orderprocessstatus'] =  '0';
						$orderarray['orderprocess'] =  '4'; 
						}	 
						if($merchantdetails['storetype']=='Theatre'){
						$orderarray['orderprocessstatus'] =  '0';
						}
						if($orderarray['orderprocess']=='3'){
						$orderarray['showaddmore'] =  '0';
						$orderarray['feedbackstatus'] =  'true'; 
						}
						$sqlorderproducts = "select * from order_products where order_id = '".$orderlist['ID']."' and merchant_id = '".$orderlist['merchant_id']."' and user_id = '".$val['header_user_id']."' order by inc asc";
						$orderproducts = Yii::$app->db->createCommand($sqlorderproducts)->queryAll();
						if($orderproducts){
						foreach($orderproducts as $orderproduct){
							$productaarray = array();
						$productaarray['id'] = $orderproduct['ID'];
						$productaarray['order'] = $orderproduct['inc'];
						$productaarray['name'] = \app\helpers\Utility::product_details($orderproduct['product_id'],'title');
						$productaarray['count'] = $orderproduct['count'];
						$productaarray['price'] = $orderproduct['price'];
						$productaarray['reorder'] = $orderproduct['reorder'];
								
						$totalproductaarray[] = $productaarray;
							}
						}
						$orderarray['products'] =  array_filter($totalproductaarray);
					$totalordersarray[] = $orderarray;
					}
				 	$payload = array('orders'=>$totalordersarray); 
					}else{
					$payload = array('status'=>'0','message'=>'Order not found!!');
					}
		return $payload;
	}
	public function order($val){
					$date = date('Y-m-d');
					$orderid = $val['orderid'];
					//$orderlist = selectQuery("orders","ID = '".$orderid."'");
					$sqlorderlist = 'select * from orders where ID = \''.$orderid.'\'';
					$orderlist = Yii::$app->db->createCommand($sqlorderlist)->queryOne();
					if(!empty($orderlist)){
						$sqlmerchantdetails = "select * from merchant where ID = '".$orderlist['merchant_id']."'";
						$merchantdetails = Yii::$app->db->createCommand($sqlmerchantdetails)->queryOne();
					
					$orderarray = $totalordersarray = array(); 
						$totalproductaarray = array();
						


						$minutes = $orderlist['preparetime'];
						if(!empty($orderlist['preparetime']) && $orderlist['preparetime'] > '0'  && !empty($orderlist['preparedate'])){
							$datetime1 = time();
							$datetime2 = strtotime($orderlist['preparedate']);
							$interval = $datetime1 - $datetime2;   
							$remainingsec = $orderlist['preparetime']*60 - $interval;
							$orderarray['preparetime'] =  $remainingsec;
							if($remainingsec<=0){
                                $orderarray['preparetime'] = 0;
							}
						}
						$orderarray['merchant_id'] =  $orderlist['merchant_id'];
						$orderarray['order_id'] =  $orderlist['ID'];
						$orderarray['unique_id'] =  $orderlist['order_id']; 
						$orderarray['username'] = \app\helpers\Utility::user_details($orderlist['user_id'],"name");
						$orderarray['storename'] = \app\helpers\Utility::merchant_details($orderlist['merchant_id'],"storename");
						$orderarray['verify'] = \app\helpers\Utility::merchant_details($orderlist['merchant_id'],"verify");
						$orderarray['tablename'] = \app\helpers\Utility::table_details($orderlist['tablename'],"name"); 
						$orderarray['totalamount'] =  $orderlist['amount'];
						$orderarray['couponamount'] = !empty($_POST['couponamount']) ? trim($_POST['couponamount']) : 0;
					
						$orderarray['paymenttype'] =  $orderlist['paymenttype']=='cash' ? 'Cash' : 'Online';
						$orderarray['orderprocesstext'] =  \app\helpers\Utility::orderstatus_details($orderlist['orderprocess']);
						$orderarray['orderprocess'] = $orderlist['orderprocess'];
						$orderarray['paidstatus'] =   $orderlist['paidstatus'];  
						$orderarray['enckey'] =  \app\helpers\Utility::encrypt($orderlist['merchant_id'].','.$orderlist['tablename']); 
	                    $orderarray['serviceboy'] = \app\helpers\Utility::serviceboy_details($orderlist['serviceboy_id'],"name");
	                    $orderarray['logo'] = !empty($merchantdetails['logo']) ? MERCHANT_LOGO.$merchantdetails['logo'] : '';
						$orderarray['coverpic'] = !empty($merchantdetails['coverpic']) ? MERCHANT_LOGO.$merchantdetails['coverpic'] : ''; 
						$orderarray['showaddmore'] = $orderlist['orderprocess'] == '3' ? '0' : '1' ;
						$orderarray['tax'] = $orderlist['tax'];
						$orderarray['tip'] = $orderlist['tips'];
						$orderarray['subscription'] = $orderlist['subscription'];

						//$orderproducts = selectloopQuery("order_products","order_id = '".$orderlist['ID']."' and merchant_id = '".$orderlist['merchant_id']."' and user_id = '".$orderlist['user_id']."'");
						$sqlorderproducts = 'select * from order_products where order_id = \''.$orderlist['ID'].'\' and merchant_id = \''.$orderlist['merchant_id'].'\' and user_id = \''.$orderlist['user_id'].'\'';
						$orderproducts = Yii::$app->db->createCommand($sqlorderproducts)->queryAll();
						if(count($orderproducts) > 0){
						foreach($orderproducts as $orderproduct){
							$productaarray = array();
						$productaarray['id'] = $orderproduct['ID'];
						$productaarray['order'] = $orderproduct['inc'];
						$productaarray['name'] = \app\helpers\Utility::product_details($orderproduct['product_id'],'title');
						$productaarray['count'] = $orderproduct['count'];
						$productaarray['price'] = $orderproduct['price'];
						$productaarray['reorder'] = $orderproduct['reorder'];
						$totalproductaarray[] = $productaarray;
							}
						}
						$orderarray['products'] =  array_filter($totalproductaarray);
				 	$payload = array('status'=>'1','orders'=>$orderarray); 
					}else{
					$payload = array('status'=>'0','message'=>'Order not found!!');
					}
		return $payload;
	}
	public function arrangeorder($val){
			$date = date('Y-m-d');
					if(!empty($val['orderid'])&&!empty($val['orderproductid'])&&!empty($val['inc'])){
					$orderid = $val['orderid'];
					$orderproductid = $val['orderproductid'];
					$inc = $val['inc']; 
					//$orderlist = selectQuery("orders","ID = '".$orderid."'");
					$sqlorderlist = 'select * from orders where ID = \''.$orderid.'\'';
					$orderlist = Yii::$app->db->createCommand($sqlorderlist)->queryOne();
					$sqltotalorders = "select count(*) as count from order_products where order_id = '".$orderlist['ID']."'";
					$totalorders = Yii::$app->db->createCommand($sqltotalorders)->queryOne();
					if($inc<=$totalorders['count']){ 
					if(!empty($orderlist)){
					$orderarray = $totalordersarray = array(); 
						$totalproductaarray = array(); 
						//$orderproduct = selectQuery("orderproduct","order_id = '".$orderlist['ID']."' and ID = '".$orderproductid."'");
						$sqlorderproduct = 'select * from order_products where order_id = \''.$orderlist['ID'].'\' and ID = \''.$orderproductid.'\'';
						$orderproduct = Yii::$app->db->createCommand($sqlorderproduct)->queryAll();
						if(count($orderproduct) > 0){
							$sqlUpdate = "update order_products set inc = '".$inc."' where ID = '".$orderproduct['ID']."'";
							$resUpdate = Yii::$app->db->createCommand($sqlUpdate)->execute();
							
							$payload = array('status'=>'1','message'=>"Status saved!!");  
							}else{
							$payload = array('status'=>'0','message'=>'Order not found!!');
							} 
					}else{
					$payload = array('status'=>'0','message'=>'Order not found!!');
					} 
					}else{
					$payload = array('status'=>'0','message'=>'Order number should be less than '.$totalorders['count']);
					}
					}else{
					$payload = array('status'=>'0','message'=>'Invalid Parameters.');
					}
		return $payload;
	}
	public function reservationlist($val){
	    	    Yii::debug("====reservationlist parameters====".json_encode($val));
		if(!empty($val['header_user_id'])){  
							$merchantlist = $merchants = $merchanttables = array(); 
							$sqlmerchantstablearray = 'select * from table_reservations where user_id = \''.$val['header_user_id'].'\' order by ID desc';
							$merchantstablearray = Yii::$app->db->createCommand($sqlmerchantstablearray)->queryAll();
							if(!empty($merchantstablearray)){
								foreach($merchantstablearray as $merchantstable){ 
								$tablearray = array();
								$tablearray['merchant_id'] = $merchantstable['merchant_id'];
								$tablearray['storename'] = \app\helpers\Utility::merchant_details($merchantstable['merchant_id'],'storename');
								$tablearray['table'] = \app\helpers\Utility::table_details($merchantstable['table_id'],'name');
								$tablearray['bookdate'] = date('d M Y',strtotime($merchantstable['bookdate']));
								$tablearray['booktime'] = date('h:i A',strtotime($merchantstable['booktime']));
								$tablearray['status'] = $merchantstable['status'];
								$tablearray['person_name'] = $merchantstable['person_name'];
							    $tablearray['mobile_number'] = $merchantstable['mobile_number'];
							    $tablearray['number_of_person'] = $merchantstable['number_of_person'];
								$tablearray['statustext'] = \app\helpers\Utility::tablereservations_status($merchantstable['status']);
								$merchanttables[] = $tablearray;
								}
								$payload = array("status"=>'1',"reservationlist"=>$merchanttables);
							}else{ 
								$payload = array("status"=>'0',"message"=>"Reservations not found.");
							} 
					}else{
						$payload = array("status"=>'0',"message"=>"User details is empty");
					 }
		return $payload;
	}
	public function orderinvoice($val)
	{
			$date = date('Y-m-d');
					$orderid = $val['orderid'];
					$email = !empty($val['email']) ? $val['email'] : '';
					//$orderlist = selectQuery("orders","ID = '".$orderid."'");
					$sqlorderlist = 'select * from orders where ID = \''.$orderid.'\'';
					$orderlist = Yii::$app->db->createCommand($sqlorderlist)->queryOne();
					if(!empty($orderlist)){
						$sqluserdetails = "select * from users where ID = '".$orderlist['user_id']."'";
						$userdetails = Yii::$app->db->createCommand($sqluserdetails)->queryOne();
						if(!empty($userdetails['ID'])){
							$emailid = $email ?: $userdetails['email'];
						if(!empty($emailid)){
							$headers = 'MIME-Version: 1.0' . "\r\n";

								$headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

								$headers .= 'From:  info <'.MAILID.'>' . " \r\n" .

											'Reply-To:  '.MAILID.' '."\r\n" .

											'X-Mailer: PHP/' . phpversion(); 
 
							/* 	 $final_message=  file_get_contents(INVOICE_URL."invoice-template.php");   */
						$final_message=  file_get_contents(INVOICE_URL."invoice.php?orderid=".$orderlist['ID']);
						$body_text = "Order invoice from FoodQ ".$orderlist['order_id']; 
						if(mail($emailid,$body_text,$final_message,$headers)){	 
						
						$payload = array('status'=>'1','message'=>"Email sent successfully"); 
						}else{
						$payload = array('status'=>'0','message'=>'Email not found');
						}
						}else{
						$payload = array('status'=>'0','message'=>'Email not found');
						}
						}else{
						$payload = array('status'=>'0','message'=>'User not found.');
						}
					}else{
					$payload = array('status'=>'0','message'=>'Order not found!!');
					}
					return $payload;
	}
	public function tablenames($val){
	    Yii::trace("============tablenames  val======".json_encode($val));
		if(!empty($val['merchantid'])){
						$merchantid = $val['merchantid'];
					//$merchantsdata = selectQuery("merchant","status = '1' and ID = '".$merchantid."'");
					 $sqlmerchantsdata = 'select * from merchant where status = \'1\' and ID = \''.$merchantid.'\'';
					 $merchantsdata = Yii::$app->db->createCommand($sqlmerchantsdata)->queryOne();
					 if(!empty($merchantsdata)){
							$merchantlist =$merchantgallerys = $merchants = $merchanttables = array();
							$merchants['status'] = 1;
							$merchants['id'] = $merchantsdata['ID']; 
							$merchants['storename'] = $merchantsdata['storename'];
							$merchants['storetype'] = $merchantsdata['storetype']=='Restaurant' ? 'Table' : 'Seat';   
							$merchants['address'] = $merchantsdata['address']; 
							$merchants['location'] = $merchantsdata['location'];
							$merchants['city'] = $merchantsdata['city'];
							$merchants['state'] = $merchantsdata['state'];
							$merchants['mobile'] = $merchantsdata['mobile'];
							$merchants['email'] = $merchantsdata['email'];
							$merchants['latitude'] = $merchantsdata['latitude'];
							$merchants['longitude'] = $merchantsdata['longitude'];
							$merchants['description'] = $merchantsdata['description'];
							$sqlfeedbackrating = "select avg(rating) as rating from feedback where merchant_id =  '".$merchantsdata['ID']."'";
							$feedbackrating = Yii::$app->db->createCommand($sqlfeedbackrating)->queryOne();
							$merchants['rating'] = !empty($feedbackrating) ? ceil($feedbackrating['rating']) : 0;
							$merchants['logo'] = !empty($merchantsdata['logo']) ?  MERCHANT_LOGO.$merchantsdata['logo'] : '';
							$merchants['coverpic'] = !empty($merchantsdata['coverpic']) ? MERCHANT_LOGO.$merchantsdata['coverpic'] : '';
							$sqlmerchantstablesarray = 'select * from tablename where status = \'1\' and merchant_id = \''.$merchantsdata['ID'].'\'';
							$merchantstablesarray = Yii::$app->db->createCommand($sqlmerchantstablesarray)->queryAll();
							foreach($merchantstablesarray as $merchanttable){
							$merchanttables['tableid'] = $merchanttable['ID']; 
							$merchanttables['name'] = $merchanttable['name']; 
							$merchanttables['capacity'] = $merchanttable['capacity']; 
								$merchantlist[] = $merchanttables;
							}

							$merchants['tablelist'] = $merchantlist;
							$sqlmerchantstablearray = 'select * from merchant_gallery where merchant_id = \''.$merchantsdata['ID'].'\'';
							$merchantstablearray = Yii::$app->db->createCommand($sqlmerchantstablearray)->queryAll();
							if(!empty($merchantstablearray)){
								$tablearray = array();
								foreach($merchantstablearray as $merchantstable){ 
								$tablearray['image'] = MERCHANT_GALLERY_URL.$merchantstable['image']; 
								$merchantgallerys[] = $tablearray;
								}  
							} 
							$merchants['gallerylist'] = $merchantgallerys; 
							$payload = $merchants;
					}else{
						
						$payload = array("status"=>'0',"text"=>"Invalid Merchant details.");
					 }
					}else{
						$payload = array("status"=>'0',"text"=>"Merchant details is empty");
					 }
					 return $payload;
	}
	public function booktable($val){
	    	    Yii::debug('===booktable parameters==='.json_encode($val));
	    	    
			if(!empty($val['merchantid'])&&!empty($val['tableid'])&&!empty($val['bookdate'])&&!empty($val['booktime'])){
						
						$merchantid = $val['merchantid'];
						$tableid = $val['tableid'];
						$bookdate = $val['bookdate'];
						$booktime = $val['booktime'];
					//$merchantsdata = selectQuery("merchant","status = '1' and ID = '".$merchantid."'");
					 $sqlmerchantsdata = 'select * from merchant where status = \'1\' and ID = \''.$merchantid.'\'';
					 $merchantsdata = Yii::$app->db->createCommand($sqlmerchantsdata)->queryOne();
					 
					 if(!empty($merchantsdata)){
					 
					    $startTime = $merchantsdata['open_time'] + 1;
                     
                        $endTime = $merchantsdata['close_time'] - 1;
                        $vall = floatval(str_replace(":",".",$booktime));
                     
                        if($startTime <= $vall && $endTime >= $vall  )
                        {
                            
                        $merchantlist = $merchants = $merchanttables = array(); 
							//$merchantstable = selectQuery("tablename","status = '1' and merchant_id = '".$merchantsdata['ID']."' and ID = '".$tableid."'");
							$sqlmerchantstable = 'select * from tablename where status = \'1\' and merchant_id = \''.$merchantsdata['ID'].'\' and ID = \''.$tableid.'\'';
							$merchantstable = Yii::$app->db->createCommand($sqlmerchantstable)->queryOne();
							if(!empty($merchantstable)){
							    
								$tablearray = array();
								$tablearray['merchant_id'] = $merchantsdata['ID'];
								$tablearray['user_id'] =  $val['header_user_id'];
								$tablearray['table_id'] = $merchantstable['ID'];
								$tablearray['bookdate'] = $bookdate;
								$tablearray['booktime'] = $booktime;
								$tablearray['person_name'] = $val['person_name'];
							    $tablearray['mobile_number'] = $val['mobile_number'];
							    $tablearray['number_of_person'] = $val['number_of_person']; 
								$tablearray['status'] = '0';
								$tablearray['reg_date'] = date('Y-m-d h:i:s');
								//$result = insertQuery($tablearray,'table_reservations');
								$result = new \app\models\TableReservations;
								$result->attributes = $tablearray;
	                            $result->save();
								
								$payload = array("status"=>'1',"message"=>"Your reservation placed successfully.");
							}else{ 
								$payload = array("status"=>'0',"message"=>"Table does not exists.");
							}
                        }else{
                            $payload = array("status"=>'0',"message"=>$merchantsdata['storename']." table reservation will take from ".Utility::hourRange($startTime)." to ".Utility::hourRange($endTime));
                        }
					}else{
						
						$payload = array("status"=>'0',"message"=>"Invalid Merchant details.");
					 }
					}else{
						$payload = array("status"=>'0',"message"=>"Invalid Parameters.");
					 }

		return $payload;
	}
	public function gallerylist($val){
		if(!empty($val['header_user_id'])){  
							$merchantlist = $merchants = $merchanttables = array(); 
							$merchantid = $val['merchantid'];
							//$merchantstablearray = selectloopQuery("merchant_gallery"," merchant_id = '".$merchantid."'");
							$sqlmerchantstablearray = 'select * from merchant_gallery where merchant_id = \''.$merchantid.'\'';
							$merchantstablearray = Yii::$app->db->createCommand($sqlmerchantstablearray)->queryAll();
							if(!empty($merchantstablearray)){
								$tablearray = array();
								foreach($merchantstablearray as $merchantstable){ 
								$tablearray['image'] = MERCHANT_GALLERY_URL.$merchantstable['image']; 
								$merchanttables[] = $tablearray;
								}
								$payload = array("status"=>'1',"gallerylist"=>$merchanttables);
							}else{ 
								$payload = array("status"=>'0',"message"=>"Images not found");
							} 
					}else{
						$payload = array("status"=>'0',"message"=>"User details is empty");
					 }
		return $payload;
	}
public function contestlist($val){
		if(!empty($val['header_user_id'])){
			$sqlcontest = 'select ID,contest_id,contest_name,contest_start_date,contest_end_date,contest_area,contest_persons
			,contest_participants,concat(\''.CONTEST_IMAGE.'\',contest_image) contest_image,created_on
			from contest where contest_end_date >= \''.date('Y-m-d').'\'';
			$contest = Yii::$app->db->createCommand($sqlcontest)->queryAll();
			if(count($contest) > 0){
				$payload = array("status"=>'1',"contest"=>$contest);
			}else{
				$payload = array("status"=>'0',"message"=>'No Contests are available');
			}
		}else{
			$payload = array("status"=>'0',"message"=>"User details is empty");
		}
			return $payload;
	}
	public function contestdetaillist($val){
	    Yii::trace("=====contest details ======".json_encode($val));
	    if(!empty($val['header_user_id'])){
			$sqlContestDet = 'select * from contest where contest_id = \''.$val['contestId'].'\'';
			$resContestDet = Yii::$app->db->createCommand($sqlContestDet)->queryOne();
			$merchantIdIn = str_replace(",","','",$resContestDet['contest_participants']);
			$sqlcontest = 'select name,email,mobile,concat(\''.USER_LOGO.'\',profilepic) profilepic,sum(case when type = \'Credit\' then ct.coins else 0 end)-sum(case when type = \'Debit\' then ct.coins else 0 end) remain_coins,
user_id from coins_transactions ct inner join users u on u.ID = ct.user_id
where date(ct.reg_date) between \''.$resContestDet['contest_start_date'].'\' AND \''.$resContestDet['contest_end_date'].'\' ';
if(!empty($resContestDet['contest_participants'])){
$sqlcontest .= ' and ct.merchant_id in (\''.$merchantIdIn.'\')  ';	
}
if(!empty($resContestDet['contest_area'])){
	$sqlcontest .= ' and ct.merchant_id in ( select ID from merchant where location = \''.$resContestDet['contest_area'].'\' )  ';	
}
$sqlcontest .= ' group by user_id,name,email,mobile,profilepic
order by remain_coins desc limit '.$val['userCount'] ;
			$contest = Yii::$app->db->createCommand($sqlcontest)->queryAll();
			if(count($contest) > 0){
				$payload = array("status"=>'1',"contest"=>$contest);
			}else{
				$payload = array("status"=>'0',"message"=>'No Contestants are available');
			}
		}else{
			$payload = array("status"=>'0',"message"=>"User details is empty1");
		}
			return $payload;
	}
	public function verifymobile($val) {
        Yii::trace("====forgot password======".json_encode($val));
		$mobilenumber = $val['mobilenumber']; 
		if(!empty($mobilenumber)){
		    $sqlalreadyid = "SELECT * FROM users WHERE mobile = '".$mobilenumber."'";
					$alreadyid = Yii::$app->db->createCommand($sqlalreadyid)->queryOne();
					if(empty($alreadyid)){
		    
					$otp = rand(1111,9999);
			            Yii::trace("====Registration ====mobile number =====".$mobilenumber."=====".$otp);
						$message = "Hi ".$otp." is OTP for your Registration.";
						\app\helpers\Utility::otp_sms($mobilenumber,$message);
						$otpModel = new \app\models\SequenceMaster;
						$otpModel->seq_name = $mobilenumber;
						$otpModel->seq_number = $otp;
						$otpModel->merchant_id = 0;
						$otpModel->reg_date = date('Y-m-d h:i:s A');
						$otpModel->save();
						$payload = array("status"=>'1',"text"=>"OTP Sent successfully");
					}else{
					    	$payload = array("status"=>'2',"usersid"=>base64_encode($alreadyid['ID']),"text"=>"OTP Verified Successfuly");
					}
		}else{
				
			$payload = array("status"=>'0',"text"=>"Please enter the valid moble number");
		}
		return $payload;
	}
	public function verifyregisterotp($val)
	{
		if(!empty($val['mobilenumber'])){
		    		  $mobilenumber =  trim($val['mobilenumber']);
						$otp = $val['otp'];

			//$sqlvalidotp = 'select seq_number as otp from sequence_master where seq_name = \''.$mobilenumber.'\' order by reg_date desc limit 1';
			//$resvalidotp = Yii::$app->db->createCommand($sqlvalidotp)->queryOne();
			$resvalidotp['otp'] = '1234';
						if($resvalidotp['otp']==$otp){ 
							$userarray = array();	
                			$sqlprevmerchnat = "select max(ID) as id from users";
                			$resprevmerchnat = Yii::$app->db->createCommand($sqlprevmerchnat)->queryOne();
                			$prevmerchnat = $resprevmerchnat['id'];
                			$newid = $prevmerchnat+1;
                			$userarray['unique_id'] = 'FDQ'.sprintf('%06d',$newid);
                			$userarray['otp'] = (string)$otp;
                			//$userarray['name'] = 'CUST'.substr(trim($mobilenumber),-4);
                			$userarray['name'] = $val['name'];
                			$userarray['mobile'] = trim($mobilenumber); 	
                			$userarray['status'] = '1';
                			$userarray['latitude'] = $val['latitude'];
                			$userarray['longitude'] = $val['longitude'];
                			$sqlalreadyid = "SELECT * FROM users WHERE mobile = '".$userarray['mobile']."'";
					$alreadyid = Yii::$app->db->createCommand($sqlalreadyid)->queryOne();
					if(empty($alreadyid)){
					    
						$result = new \app\models\Users;
						$result->attributes =  $userarray;
						$result->reg_date = date('Y-m-d h:i:s');
						$result->mod_date = date('Y-m-d h:i:s');
						$result->save();
						if($result->save()){
						
							$payload = array("status"=>'1',"usersid"=>base64_encode($result->ID)
							,"text"=>"OTP Verified Successfuly",'user_details' => ($userarray) );	  
						}else{
						    //print_r(json_encode($result->getErrors()));
							$payload = array("status"=>'0',"text"=>"User Registration Failed",'user_details' => null);
						} 
					}else{
					    $sqlUPdate = 'update users set name = \''.$val['name'].'\',latitude = \''.$val['latitude'].'\',longitude = \''.$val['longitude'].'\'  where ID = \''.$alreadyid['ID'].'\'';
					    $resUpdate = Yii::$app->db->createCommand($sqlUPdate)->execute();
					    	$payload = array("status"=>'1',"usersid"=>base64_encode($alreadyid['ID'])
					    	,"text"=>"OTP Verified Successfuly"
					    	,'user_details' => ($alreadyid)
					    	);
					}
							
							

						}else{
					
							$payload = array("status"=>'0',"text"=>"Invalid OTP",'user_details' => null);
						}  
			
		}else{
					
			$payload = array('status'=>'0','message'=>'Invalid Parameters');
		}
		return $payload;
	}
	public function roomreservationsold($val)
	{
	    $roomreservationhotelslist = [];
        $room_reservation_types = MyConst::ROOM_RESERVATION_TYPES;
        $room_reservation_types_ids = array_keys($room_reservation_types);
        $room_reservation_types_ids_string = implode("','",$room_reservation_types_ids);

        $sqlMerchants = 'select * from merchant where status = \''.MyConst::TYPE_ACTIVE.'\' and storetype in (\''.$room_reservation_types_ids_string.'\')';
        $resMerchants = Yii::$app->db->createCommand($sqlMerchants)->queryAll();
        
        $resultMerchants =         \yii\helpers\ArrayHelper::index($resMerchants, null, 'storetype');
        
        
        for($i=0;$i<count($room_reservation_types_ids);$i++){        
            $roomreservationhotelslist[$i]['id'] = $room_reservation_types_ids[$i];  
            $roomreservationhotelslist[$i]['name'] = $room_reservation_types[$room_reservation_types_ids[$i]];  
            $roomreservationhotelslist[$i]['merchantdetails'] = $resultMerchants[$room_reservation_types_ids[$i]];
            
        
        }
        
        $payload = array("status"=>'1',"$roomreservationhotelslist"=>$roomreservationhotelslist);

	    return $payload;
	}
	public function roomreservations($val)
	{
	    $roomreservationhotelslist = [];
        $room_reservation_types = MyConst::ROOM_RESERVATION_TYPES;
        $room_reservation_types_ids = array_keys($room_reservation_types);
        $room_reservation_types_ids_string = implode("','",$room_reservation_types_ids);

        $sqlMerchants = 'select * from merchant where status = \''.MyConst::TYPE_ACTIVE.'\' and storetype in (\''.$room_reservation_types_ids_string.'\') order by storetype';
        $resMerchants = Yii::$app->db->createCommand($sqlMerchants)->queryAll();
        

        
        
        for($i=0;$i<count($resMerchants);$i++){        
            
            $sqlprices = 'select * from room_reservations where merchant_id = \''.$resMerchants[$i]['ID'].'\' order by price limit 1';
            $resPrices = Yii::$app->db->createCommand($sqlprices)->queryOne();
            
            
            $roomreservationmerchantslist[$i]['id'] = $resMerchants[$i]['ID'];
            $roomreservationmerchantslist[$i]['restname'] = $resMerchants[$i]['storename'];
            $roomreservationmerchantslist[$i]['restimage'] =  !empty($resMerchants[$i]['coverpic']) ? MERCHANT_LOGO.$resMerchants[$i]['coverpic'] : ''; 
            $roomreservationmerchantslist[$i]['restimage'] =  !empty($resMerchants[$i]['coverpic']) ? MERCHANT_LOGO.$resMerchants[$i]['coverpic'] : ''; 
            $roomreservationmerchantslist[$i]['restlat'] =  $resMerchants[$i]['latitude'] ; 
            $roomreservationmerchantslist[$i]['restlng'] =  $resMerchants[$i]['longitude'] ; 
            $roomreservationmerchantslist[$i]['servingtype'] =  $resMerchants[$i]['servingtype'] ; 
            $roomreservationmerchantslist[$i]['catname'] =  $room_reservation_types[$resMerchants[$i]['storetype']] ; 
            $roomreservationmerchantslist[$i]['price'] =   !empty($resPrices) ? $resPrices['price'] : 'Not available';
            $roomreservationmerchantslist[$i]['startrating'] =  null;
            $roomreservationmerchantslist[$i]['favorite'] =  null;
            $roomreservationmerchantslist[$i]['categories'] =  MyConst::ROOM_RESERVATION_TYPES;
        }
        
        $payload = array("status"=>'1',"$roomreservationhotelslist"=>$roomreservationmerchantslist);

	    return $payload;
	}
}
?>