<?php

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
		// Add Message
		if($bean->sales_stage === "Closed Won" && ($bean->sales_stage != $bean->fetched_row['sales_stage']) && $bean->amount>=10000)
			SugarApplication::appendErrorMessage('You have closed and won an opportunity greater than 10000');
	}
	
	function handleException(&$bean, $event, $arguments=''){

	}
	
	function processRecord(&$bean, $event, $arguments=''){

	}

}

?>
