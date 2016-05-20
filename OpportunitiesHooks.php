<?php

/*********************************************************************************
* This code was developed by:
* Audox Ingeniería Ltda.
* You can contact us at:
* Web: www.audox.cl
* Email: info@audox.cl
* Skype: audox.ingenieria
********************************************************************************/

class OpportunitiesHooks{
	
	function afterDelete(&$bean, $event, $arguments=''){
		
	}
	
	function afterRelationshipAdd(&$bean, $event, $arguments=''){
		
	}
	
	function afterRelationshipDelete(&$bean, $event, $arguments=''){
		
	}
	
	function afterRestore(&$bean, $event, $arguments=''){
		
	}
	
	function afterRetrieve(&$bean, $event, $arguments=''){
		
	}
	
	function afterSave(&$bean, $event, $arguments=''){
		// Create Task and Call for new Opportunities
		$this->CreateTaskAndCallForNewOpportunity($bean);
	}
	
	function beforeDelete(&$bean, $event, $arguments=''){
		
	}
	
	function beforeRelationshipAdd(&$bean, $event, $arguments=''){
		
	}
	
	function beforeRelationshipDelete(&$bean, $event, $arguments=''){
		
	}
	
	function beforeRestore(&$bean, $event, $arguments=''){
		
	}
	
	function beforeSave(&$bean, $event, $arguments=''){
		// Send an Email to all users within "Sales Manager" role when an Opportunity greater than certain amount change from "Qualification" to "Negotiation"
		$this->NotifySalesManagers($bean);
		
		// Send data to ERP or other external app when an Opportunity change to "Closed Won"
		$this->SendToERP($bean);
		
		// Create Up Selling Tasks
		$this->CreateUpSellingTasks($bean);
		
		// Create Cross Selling Opportunities
		$this->CreateCrossSellingOpportunities($bean);
	}
	
	function handleException(&$bean, $event, $arguments=''){
		
	}
	
	function processRecord(&$bean, $event, $arguments=''){
		
	}
	
	function SendEmail($emailsTo, $emailSubject, $emailBody){
		$emailObj = new Email();
		$defaults = $emailObj->getSystemDefaultEmail();
		$mail = new SugarPHPMailer();
		$mail->setMailerForSystem();
		$mail->From = $defaults['email'];
		$mail->FromName = $defaults['name'];
		$mail->ClearAllRecipients();
		$mail->ClearReplyTos();
		$mail->Subject=from_html($emailSubject);
		$mail->Body=$emailBody;
		$mail->AltBody=from_html($emailBody);
		$mail->prepForOutbound();
		foreach($emailsTo as &$value){
			$mail->AddAddress($value);
		}
		if(@$mail->Send()){
		}
	}
	
	function CreateTaskAndCallForNewOpportunity($bean){
		$timeDate = new TimeDate();
		
		if(empty($bean->fetched_row['id'])){
			
			$task = new Task();
			$task->name = "Send Proposal";
			$task->priority = "High";
			$task->status = "Not Started";
			$task->date_due = $timeDate->getNow(true)->modify("+1 days")->asDb();
			$task->parent_type = "Opportunities";
			$task->parent_id = $bean->id;
			$task->assigned_user_id = $bean->assigned_user_id;
			$task->save();
			
			$call = new Call();
			$call->name = "Follow up";
			$call->direction = "Outbound";
			$call->status = "Planned";
			$call->duration_hours = 0;
			$call->duration_minutes = 15;
			$call->date_start = $timeDate->getNow(true)->modify("+2 days")->asDb();
			$call->parent_type = "Opportunities";
			$call->parent_id = $bean->id;
			$call->assigned_user_id = $bean->assigned_user_id;
			$call->save();
			
		}
	}
	
	function CreateUpSellingTasks($bean){
		if($bean->fetched_row['name'] !== $bean->name && $bean->name === "Computadores Estandar"){
			$task = new Task();
			$task->name = "Ofrecer Computadores Premium";
			$task->parent_type = "Opportunities";
			$task->parent_id = $bean->id;
			$task->assigned_user_id = $bean->assigned_user_id;
			$task->save();
			SugarApplication::appendErrorMessage('You have a new Task for Up Selling Opportunity');
		}
	}
	
	function CreateCrossSellingOpportunities($bean){
		if($bean->fetched_row['name'] !== $bean->name && $bean->name === "Monitores"){
			$opportunity = new Opportunity();
			$opportunity->name = "Impresoras";
			$opportunity->account_id = $bean->account_id;
			$opportunity->sales_stage = "Prospecting";
			$opportunity->assigned_user_id = $bean->assigned_user_id;
			$opportunity->save();
			SugarApplication::appendErrorMessage('You have a new Opportunity for Cross Selling Opportunity');
		}
	}
	
	function NotifySalesManagers($bean){
		global $sugar_config;
		$amount_limit = 1000;
		if($bean->sales_stage === "Negotiation/Review" && $bean->fetched_row['sales_stage'] === "Proposal/Price Quote" && $bean->amount >= $amount_limit){
			SugarApplication::appendErrorMessage('You have changed the opportunity '.$bean->name.' (greater than '.$amount_limit.') to Negotiation/Review.');
			$emailsTo = array();
			$emailSubject = "Opportunity Alert";
			$emailBody = "The Opportunity ".$bean->name." has changed to Negotiation/Review<br />
			You can see the opportunity here:<br />
			<a href=\"".$sugar_config['site_url']."/index.php?module=Opportunities&action=DetailView&record=".$bean->id."\">".$bean->name."</a>";
			$role_id = "<sales-manager-role-id>";
			$aclrole = new ACLRole();
			if(!is_null($aclrole->retrieve($role_id))){
				$users = $aclrole->get_linked_beans('users','User');
				foreach($users as $user){
					$emailsTo[] = $user->email1;
				}
			}
			$this->SendEmail($emailsTo, $emailSubject, $emailBody);
		}
	}
	
	function SendToERP($bean){
		if($bean->sales_stage === "Closed Won" && ($bean->sales_stage != $bean->fetched_row['sales_stage'])){
			$account = new Account();
			if(!is_null($account->retrieve($bean->account_id))){
				$url = "<your-erp-rest-url>";
				$fields = array(
					'account_id' => $account->id,
					'account_name' => $account->name,
					'opportunity_id' => $bean->id,
					'opportunity_name' => $bean->name,
					'opportunity_amount' => $bean->amount,
				);
				$curl = curl_init($url);
				curl_setopt($curl, CURLOPT_POST, true); 
				curl_setopt($curl, CURLOPT_POSTFIELDS, $fields);
				curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
				$response = curl_exec($curl);
				SugarApplication::appendErrorMessage('The Opportunity '.$bean->name.' has been sent to ERP');
			}
		}
	}

}

?>
