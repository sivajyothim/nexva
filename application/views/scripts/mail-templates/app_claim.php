Hi Content Administrator,<br /><br />

<?=$this->cp_company_name?> has claimed application. <?=$this->content_name?>. Details are as follows:  <br/><br/>

<strong>Company:</strong> <?=$this->cp_company_name?><br/><br/>

<strong>Contact Person/Designation/Email:</strong> <?=$this->cp_name?> / <?=$this->designation?> / <?=$this->cp_email?><br/><br/>

Originating IP Address: <?=$this->ip?> <br/><br/>

<a href="<?='http://'.Zend_Registry::get('config')->nexva->application->admin->url."/user/edit/id/" . $this->content_id;?>">

Click here to view details about this CP </a> or 
<a href="<?='http://'.Zend_Registry::get('config')->nexva->application->admin->url."/product/display/id/" . $this->content_id;?>">
Click here to view the content in question </a> 

<br/><br/>

Thank you.<br />
- neXva.
