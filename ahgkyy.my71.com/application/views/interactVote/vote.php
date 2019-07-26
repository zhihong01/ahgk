<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head> 
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <!-- InstanceBeginEditable name="doctitle" -->
        <title>宣城市城管局</title>
        <!-- InstanceEndEditable -->   
        <link href="/media/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/media/js/jquery-1.8.3.min.js"></script>

        <!-- InstanceBeginEditable name="head" -->
        <script type="text/javascript" src="/media/js/jquery.form.js"></script> 
        <script type="text/javascript" src="/media/js/noty/packaged/jquery.noty.packaged.min.js"></script>
        <script type="text/javascript" src="/media/js/noty/showNoty.js"></script>
        <!-- InstanceEndEditable -->
    </head>
    <body>
        <div id="zoom" class="is-contentbox">
            <div>
                <?php if (!empty($this_form)) { ?>
                    <table width="100%" border="0" cellpadding="0" cellspacing="0"  class="table table-hover">
                        <tbody>
                          
                            <tr bgcolor="#fbfbe2">
                                <td style="text-align: center;">
                                    调查日期：<?php echo($this_form['startdate']); ?>
                                </td>
                                <td style="text-align: center;">
                                    截至日期：<?php echo($this_form['overdate']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: left;">
                                    <?php echo($this_form['description']); ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="2" style="text-align: right;">
                                </td>
                            </tr>

                        </tbody>
                    </table>
                    <hr/>
                    <form method="post" action="/interactVote/vote/" class="form-horizontal" id="voteForm" name="voteForm">
                        <input type="hidden" id="vote_id" name="data[vote_id]" value="<?php echo($this_form['_id']); ?>" />

                        <div class="tab-content">
                            <?php foreach ($form_list as $key => $value) {
                                ?>
                                <input type="hidden" value="<?php echo $value['_id']; ?>" name="form_data[<?php echo $value['_id']; ?>]" />
                                <input type="hidden" value="<?php echo $value['type']; ?>" name="form_data[<?php echo $value['_id']; ?>][type]" />

                                <div class="control-group">
                                    <label class="control-label"><?php echo $value['name']; ?><?php
                                        if ($value['requried']) {
                                            echo "<font color='red'> *</font>";
                                        }
                                        ?>：</label><br>
                                        <?php
                                        if ($value['type'] == 'radio' || $value['type'] == 'checkbox') {
                                            ?>		

                                            <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                            <div class="controls">
            <?php foreach ($value['data']['option'] as $k => $val) { ?>
                                                    <label class="<?php echo $value['attr']; ?>">
                                                        <input name="form_data[<?php echo $value['_id']; ?>][data][]" type="<?php echo $value['type']; ?>" value="<?php echo $k; ?>" class="ace"/><span class="lbl"><?php echo $val; ?></span>
                                                    </label>
                                            <?php } ?>
                                            </div>

                                            <?php
                                        } elseif ($value['type'] == 'select') {
                                            ?>
                                            <div class="controls">
                                                <select class="input-xlarge valtype" <?php echo $value['attr']; ?> name="form_data[<?php echo $value['_id']; ?>][data][]">
                                                    <?php foreach ($value['data']['option'] as $k => $val) { ?>
                                                        <option value="<?php echo $k; ?>"><?php echo $val; ?></option>
            <?php } ?>
                                                </select>
                                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                            </div>
                                            <?php
                                        } elseif ($value['type'] == 'input') {
                                            ?>
                                            <div class="controls">
                                                <input type="text" name="form_data[<?php echo $value['_id']; ?>][data][]" placeholder="<?php echo $value['data']['placeholder']; ?>" class="input-xlarge valtype" data-valtype="placeholder" />
                                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                            </div>
                                            <?php
                                        } elseif ($value['type'] == 'textarea') {
                                            ?>
                                            <div class="controls">
                                                <textarea name="form_data[<?php echo $value['_id']; ?>][data][]" cols="60" rows="" class="input-xxlarge" data-valtype="textarea" placeholder="<?php echo $value['data']['placeholder']; ?>"></textarea>
                                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                            </div>
                                            <?php
                                        } elseif ($value['type'] == 'label') {
                                            ?>
                                            <div class="controls">
                                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                            </div>
                                <?php }
                                ?>
                                </div>
    <?php } ?>	
                        </div>


                        <table width="100%" border="0" cellpadding="0" cellspacing="0"  class="table table-hover">
    <?php if ($this_form['is_realname']) { ?>

                                <tr>
                                    <td colspan="4">
                                        投票人相关信息 (请填入所有信息)
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        真实姓名：<span class="required"></span>
                                    </td>
                                    <td>
                                        <input type="text" id="name" name="data[name]" value="" 
                                               required data-validation-required-message="请填入姓名"
                                               />
                                    </td>
                                    <td>
                                        身份证号：<span class="required"></span>
                                    </td>
                                    <td>
                                        <input type="text" id="voter_paper_id" name="data[voter_paper_id]" value="" 
                                               onkeyup="this.value = this.value.replace(/[^0-9a-zA-Z]+/g, '')"
                                               onafterpaste="this.value=this.value.replace(/[^0-9a-zA-Z]+/g,'')"
                                               required data-validation-required-message="请填入身份证号"
                                               />
                                    </td>
                                </tr>
                                <tr>
                                    <td>
                                        电话：<span class="required"></span>
                                    </td>
                                    <td>
                                        <input type="text" id="voter_tel" name="data[voter_tel]" value=""
                                               required data-validation-required-message="请填入联系电话"
                                               />
                                    </td>
                                    <td>
                                        地址：<span class="required"></span>
                                    </td>
                                    <td>
                                        <input type="text" id="voter_addr" name="data[voter_addr]" value=""  
                                               required data-validation-required-message="请填入地址"
                                               />
                                    </td>
                                </tr>

    <?php } ?>
                            <tr>
                                <td>
                                </td>
                                <td colspan="2">
                                    <?php if ($this_form['can_vote']) { ?>
                                        <button type="submit" class="btn btn-primary btn-sm">提交投票</button>
                                        <input type="button" name="clear_btn"  class="btn btn-sm" id="clear_btn" value="重置"/>
                                    <?php } else { ?>
                                        无法投票，（您已经投过票了，或者投票已经结束）
                                    <?php } ?>
                                </td>
                                <td>
                                    <?php if ($this_form['can_view']) { ?>
                                        <a href="/interactVote/viewResult/?_id=<?php echo($this_form['_id']); ?>">[查看结果]</a>
    <?php } ?>
                                </td>
                            </tr>
                            <tr>
                                <td colspan="4">
    <?php echo($this_form['status_type'] ); ?>
                                </td>
                            </tr>

                        </table>
                    </form>
<?php } ?>
                <script type="text/javascript">
                                           $(function() {
                                               $("#voteForm").submit(function() {
                                                   var options = {
                                                       type: 'POST',
                                                       dataType: "json",
                                                       success: function(data) {
                                                           showNoty(data.status, 5, data.msg, data.time, true);
                                                           if (data.status === 2) {
                                                               setTimeout(function() {
                                                                   location.reload(true);
                                                               }, 2000);
                                                           }
                                                       }
                                                   };
                                                   $(this).ajaxSubmit(options);
                                                   return false;
                                               });
                                           });
                </script>
            </div>
        </div>
    </body>
    <!-- InstanceEnd --></html>