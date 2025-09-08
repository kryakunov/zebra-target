<label class='label'><b>Собирать сообщества, в которых участников:</b></label> 
<div class='row'>	
    <div class='col-md-3'>
    <input type="text" size="20" name="ot"   class="textbox" placeholder="от"  value="<?php if(isset($request['ot'])) echo $request['ot']; ?>" aria-describedby="button-addon2" /> 
    </div>
     
    <div class='col-md-3'>
        <input type="text" size="20" name="do"  class="textbox" placeholder="до (включительно)"  value="<?php if(isset($request['do'])) echo $request['do']; ?>" aria-describedby="button-addon2" /> 
    </div>
</div>