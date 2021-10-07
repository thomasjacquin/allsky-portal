<?php

/**
*
*
*/
function DisplayDashboard_eth0(){

	$status = new StatusMessages();

	exec( 'ifconfig eth0', $return );

	$strEth0 = implode( " ", $return );
	$strEth0 = preg_replace( '/\s\s+/', ' ', $strEth0 );

	// Parse results from ifconfig/iwconfig
	preg_match( '/ether ([0-9a-f:]+)/i',$strEth0,$result );
	$strHWAddress = $result[1];
	preg_match( '/inet ([0-9.]+)/i',$strEth0,$result );
	if (isset($result[1]))
		$strIPAddress = $result[1];
	else
		$strIPAddress = "[not set]";
	preg_match( '/netmask ([0-9.]+)/i',$strEth0,$result );
	if (isset($result[1]))
		$strNetMask = $result[1];
	else
		$strNetMask = "[not set]";
	preg_match( '/RX packets (\d+)/',$strEth0,$result );
	$strRxPackets = $result[1];
	preg_match( '/TX packets (\d+)/',$strEth0,$result );
	$strTxPackets = $result[1];
	preg_match_all( '/bytes (\d+ \(\d+.\d+ [K|M|G]iB\))/i',$strEth0,$result );
	if (isset($result[1][0])) {
		$strRxBytes = $result[1][0];
		$strTxBytes = $result[1][1];
	} else {
		$strRxBytes = 0;
		$strTxBytes = 0;
	}

	if(strpos( $strEth0, "UP" ) !== false && strpos( $strEth0, "RUNNING" ) !== false ) {
		$status->addMessage('Interface is up', 'success');
		$eth0up = true;
	} else {
		$eth0up = false;
		$status->addMessage('Interface is down', 'warning');
	}

	if( isset($_POST['ifdown_eth0']) ) {
		exec( 'ifconfig eth0 | grep -i running | wc -l',$test );
		if($test[0] == 1) {
			exec( 'sudo ifdown eth0',$return );
		} else {
			echo 'Interface already down';
		}
	} elseif( isset($_POST['ifup_eth0']) ) {
		exec( 'ifconfig eth0 | grep -i running | wc -l',$test );
		if($test[0] == 0) {
			exec( 'sudo ifup eth0',$return );
		} else {
			echo 'Interface already up';
		}
	}
?>

  <div class="row">
      <div class="col-lg-12">
          <div class="panel panel-primary">
            <div class="panel-heading"><i class="fa fa-tachometer-alt fa-fw"></i>LAN Dashboard</div>
              <div class="panel-body">
                <p><?php $status->showMessages(); ?></p>
                  <div class="row">
                        <div class="col-md-6">
                        <div class="panel panel-default">
                          <div class="panel-body">
                           <h4>Interface Information</h4>
                           <div class="info-item">Interface Name</div> wlan0</br>
                           <div class="info-item">IP Address</div>     <?php echo $strIPAddress ?></br>
                           <div class="info-item">Subnet Mask</div>    <?php echo $strNetMask ?></br>
                           <div class="info-item">Mac Address</div>    <?php echo $strHWAddress ?></br></br>

                           <h4>Interface Statistics</h4>
                           <div class="info-item">Received Packets</div>    <?php echo $strRxPackets ?></br>
                           <div class="info-item">Received Bytes</div>      <?php echo $strRxBytes ?></br></br>
                           <div class="info-item">Transferred Packets</div> <?php echo $strTxPackets ?></br>
                           <div class="info-item">Transferred Bytes</div>   <?php echo $strTxBytes ?></br>
                          </div><!-- /.panel-body -->
                        </div><!-- /.panel-default -->
                        </div><!-- /.col-md-6 -->
                   </div><!-- /.row -->

                  <div class="col-lg-12">
                    <div class="row">
                      <form action="?page=eth0_info" method="POST">
                      <?php
                      if ( !$eth0up ) {
                          echo '<input type="submit" class="btn btn-success" value="Start eth0" name="ifup_eth0" />';
                      } else {
                          echo '<input type="submit" class="btn btn-warning" value="Stop eth0" name="ifdown_eth0" />';
                      }
                      ?>
                      <input type="button" class="btn btn-outline btn-primary" value="Refresh" onclick="document.location.reload(true)" />
                     </form>
                   </div>
                 </div>

              </div><!-- /.panel-body -->
              <div class="panel-footer">Information provided by ifconfig</div>
            </div><!-- /.panel-default -->
        </div><!-- /.col-lg-12 -->
    </div><!-- /.row -->
<?php 
}
?>
