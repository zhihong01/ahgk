<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head> 
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>宣城市城管局</title>
        <link href="/media/css/bootstrap.min.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/media/js/jquery-1.8.3.min.js"></script>
    </head>
    <body>
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
                            &nbsp;
                        </td>
                    </tr>
                    <tr>
                        <td style="text-align: center;">
                            票数：<?php echo($this_form['voter_count']); ?> / 投票人记录数：<?php echo($vote_log_count); ?>
                        </td>
                        <td style="text-align: center;">
                            当前时间：<?php echo(date("Y-m-d H:i:s")); ?>
                        </td>
                    </tr>
                </tbody>
            </table>
            <hr/>
            <input type="hidden" id="form_id" name="data[form_id]" value="<?php echo($this_form['_id']); ?>" />   
            <div class="space-6"></div>

            <div class="tab-content">
                <?php foreach ($form_list as $key => $value) {
                    ?>
                    <input type="hidden" value="<?php echo $value['_id']; ?>" name="form_data[<?php echo $value['_id']; ?>]" />
                    <input type="hidden" value="<?php echo $value['type']; ?>" name="form_data[<?php echo $value['_id']; ?>][type]" />
                    <div class="control-group">
                        <label class="control-label"><?php echo $value['name']; ?>：</label><br>
                            <?php
                            if ($value['type'] == 'radio' || $value['type'] == 'checkbox') {
                                ?>		

                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                <div class="controls">
                                    <?php
                                    foreach ($value['result'] as $k => $val) {
                                        if ($value['form_total'] != 0) {
                                            $rate = round(($val['total'] / $value['form_total']) * 100) . '%';
                                        }
                                        ?>
                                        <label class="radio">
                                            <input type="<?php echo $value['type']; ?>" class="ace"/><span class="lbl"><?php echo $val['name']; ?></span>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $rate; ?>;"></div>
                                                <label><?php echo $val['total']; ?>票，所占比例：<?php echo $rate; ?></label>
                                            </div>
                                        </label>

                                <?php } ?>
                                </div>
                                <?php
                            } elseif ($value['type'] == 'select') {
                                ?>		
                                <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                <div class="controls">
                                    <?php
                                    foreach ($value['result'] as $k => $val) {
                                        $rate = round(($val['total'] / $value['form_total']) * 100) . '%';
                                        ?>
                                        <label class="radio">
                                            <select>
                                                <option><?php echo $val['name']; ?></option>
                                            </select>
                                            <div class="progress">
                                                <div class="progress-bar" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: <?php echo $rate; ?>;"></div>
                                                <label><?php echo $val['total']; ?>票，所占比例：<?php echo $rate; ?></label>
                                            </div>
                                        </label>

                                <?php } ?>
                                </div>
                                <?php
                            } elseif ($value['type'] == 'input' || $value['type'] == 'textarea') {
                                ?>
                                <label><?php echo $value['result']['total']; ?>人次提供意见</label>
                                <?php
                            } elseif ($value['type'] == 'label') {
                                ?>
                                <div class="controls">
                                    <label class="help-inline info disabled"><?php echo $value['description']; ?></label>
                                </div>
                                <?php
                            }
                        }
                        ?>
                </div>	
            </div>
<?php } ?>
    </body>
    <!-- InstanceEnd --></html>