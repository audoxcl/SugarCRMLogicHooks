# SugarCRM Logic Hooks Template

In this example you can see a ready to use template for these Logic Hooks:

* afterDelete
* afterRelationshipAdd
* afterRelationshipDelete
* afterRestore
* afterRetrieve
* afterSave
* beforeDelete
* beforeRelationshipAdd
* beforeRelationshipDelete
* beforeRestore
* beforeSave
* handleException
* processRecord

We have included a process example as follows:

1. When creating a new Opportunity a Task and a Call records are created.
2. When an Opportunity change from "Proposal/Price Quote" to "Negotiation/Review" an email is sended to all users within role "Sales Manager".
3. When an Opportunity change to "Closed Won" a curl call is sended to an external app, that could be your ERP for example.

## Installation

Just zip the php files and install as any other module in your SugarCRM instance.
