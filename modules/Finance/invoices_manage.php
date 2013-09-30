<?
/*
Gibbon, Flexible & Open School System
Copyright (C) 2010, Ross Parker

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program. If not, see <http://www.gnu.org/licenses/>.
*/

session_start() ;

if (isActionAccessible($guid, $connection2, "/modules/Finance/invoices_manage.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print "You do not have access to this action." ;
	print "</div>" ;
}
else {
	//Proceed!
	print "<div class='trail'>" ;
	print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>Home</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . getModuleName($_GET["q"]) . "</a> > </div><div class='trailEnd'>Manage Invoices</div>" ;
	print "</div>" ;
	
	$issueReturn = $_GET["issueReturn"] ;
	$issueReturnMessage ="" ;
	$class="error" ;
	if (!($issueReturn=="")) {
		if ($issueReturn=="success0") {
			$issueReturnMessage ="Issue was successful." ;	
			$class="success" ;
		}
		if ($issueReturn=="success1") {
			$issueReturnMessage ="Issue was successful, but one or more requested emails could not be sent." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $issueReturnMessage;
		print "</div>" ;
	} 
	
	$deleteReturn = $_GET["deleteReturn"] ;
	$deleteReturnMessage ="" ;
	$class="error" ;
	if (!($deleteReturn=="")) {
		if ($deleteReturn=="success0") {
			$deleteReturnMessage ="Delete was successful." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $deleteReturnMessage;
		print "</div>" ;
	} 
	
	$bulkReturn = $_GET["bulkReturn"] ;
	$bulkReturnMessage ="" ;
	$class="error" ;
	if (!($bulkReturn=="")) {
		if ($bulkReturn=="fail0") {
			$bulkReturnMessage ="Add failed because you do not have access to this action." ;	
		}
		else if ($bulkReturn=="fail2") {
			$bulkReturnMessage ="Add failed due to a database error." ;	
		}
		else if ($bulkReturn=="fail3") {
			$bulkReturnMessage ="Add failed because your inputs were invalid." ;	
		}
		else if ($bulkReturn=="fail5") {
			$bulkReturnMessage ="Some elements of your bulk process failed, but others were successful." ;	
		}
		else if ($bulkReturn=="success0") {
			$bulkReturnMessage ="Bulk process was successful." ;	
			$class="success" ;
		}
		if ($bulkReturn=="success1") {
			$bulkReturnMessage ="Bulk process was successful, but one or more requested emails could not be sent." ;	
			$class="success" ;
		}
		print "<div class='$class'>" ;
			print $bulkReturnMessage;
		print "</div>" ;
	}
	
	print "<p>" ;
		print "This section allows you to generate, view, edit and delete invoices, either for an individual or in bulk. You can use the filters below to pick up certain invoices types (e.g. those that are overdue) or view all invoices types for a particular user. Invoices, reminders and receipts can be send out using the Email function, shown in the right-hand side menu.<br/>" ;
		print "<br/>" ;
		print "When you create invoices using the billing schedule or pre-defined fee features, the invoice will remain linked to these areas whilst pending. Thus, changes made to the billing schedule and pre-defined fees will be reflected in any pending invoices. Once invoices are issued, this link is removed, and the values are fixed at the levels when the invoice was issued." ;
	print "</p>" ;
	
	$gibbonSchoolYearID=$_GET["gibbonSchoolYearID"] ;
	if ($gibbonSchoolYearID=="") {
		$gibbonSchoolYearID=$_SESSION[$guid]["gibbonSchoolYearID"] ;
		$gibbonSchoolYearName=$_SESSION[$guid]["gibbonSchoolYearName"] ;
	}
	if ($_GET["gibbonSchoolYearID"]!="") {
		try {
			$data=array("gibbonSchoolYearID"=>$_GET["gibbonSchoolYearID"]); 
			$sql="SELECT * FROM gibbonSchoolYear WHERE gibbonSchoolYearID=:gibbonSchoolYearID" ;
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		if ($result->rowcount()!=1) {
			print "<div class='error'>" ;
				print "The specified year does not exist." ;
			print "</div>" ;
		}
		else {
			$row=$result->fetch() ;
			$gibbonSchoolYearID=$row["gibbonSchoolYearID"] ;
			$gibbonSchoolYearName=$row["name"] ;
		}
	}
	
	if ($gibbonSchoolYearID!="") {
		print "<h2>" ;
			print $gibbonSchoolYearName ;
		print "</h2>" ;
		
		print "<div class='linkTop'>" ;
			//Print year picker
			if (getPreviousSchoolYearID($gibbonSchoolYearID, $connection2)!=FALSE) {
				print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage.php&gibbonSchoolYearID=" . getPreviousSchoolYearID($gibbonSchoolYearID, $connection2) . "'>Previous Year</a> " ;
			}
			else {
				print "Previous Year " ;
			}
			print " | " ;
			if (getNextSchoolYearID($gibbonSchoolYearID, $connection2)!=FALSE) {
				print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage.php&gibbonSchoolYearID=" . getNextSchoolYearID($gibbonSchoolYearID, $connection2) . "'>Next Year</a> " ;
			}
			else {
				print "Next Year " ;
			}
		print "</div>" ;
	
		$status=$_GET["status"] ;
		if ($status=="") {
			$status="Pending" ;
		}
		$gibbonFinanceInvoiceeID=$_GET["gibbonFinanceInvoiceeID"] ;
		$monthOfIssue=$_GET["monthOfIssue"] ;
		$gibbonFinanceBillingScheduleID=$_GET["gibbonFinanceBillingScheduleID"] ;
	
		print "<h3>" ;
			print "Filters" ;
		print "</h3>" ;
		print "<form method='get' action='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Finance/invoices_manage.php'>" ;
			print "<table class='noIntBorder' cellspacing='0' style='width: 100%'>" ;
				?>
				<tr>
					<td> 
						<b>Status</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<?
						print "<select name='status' id='status' style='width:302px'>" ;
							$selected="" ;
							if ($status=="%") {
								$selected="selected" ;
							}
							print "<option $selected value='%'>All</option>" ;
							$selected="" ;
							if ($status=="Pending") {
								$selected="selected" ;
							}
							print "<option $selected value='Pending'>Pending</option>" ;
							$selected="" ;
							if ($status=="Issued") {
								$selected="selected" ;
							}
							print "<option $selected value='Issued'>Issued</option>" ;
							$selected="" ;
							if ($status=="Issued - Overdue") {
								$selected="selected" ;
							}
							print "<option $selected value='Issued - Overdue'>Issued - Overdue</option>" ;
							$selected="" ;
							if ($status=="Paid") {
								$selected="selected" ;
							}
							print "<option $selected value='Paid'>Paid</option>" ;
							$selected="" ;
							if ($status=="Paid - Late") {
								$selected="selected" ;
							}
							print "<option $selected value='Paid - Late'>Paid - Late</option>" ;
							$selected="" ;
							if ($status=="Cancelled") {
								$selected="selected" ;
							}
							print "<option $selected value='Cancelled'>Cancelled</option>" ;
							$selected="" ;
							if ($status=="Refunded") {
								$selected="selected" ;
							}
							print "<option $selected value='Refunded'>Refunded</option>" ;
						print "</select>" ;
						?>
					</td>
				</tr>
				<tr>
					<td> 
						<b>Student</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<?
						try {
							$dataPurpose=array(); 
							$sqlPurpose="SELECT surname, preferredName, gibbonFinanceInvoiceeID FROM gibbonFinanceInvoicee JOIN gibbonPerson ON (gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID) ORDER BY surname, preferredName" ;
							$resultPurpose=$connection2->prepare($sqlPurpose);
							$resultPurpose->execute($dataPurpose);
						}
						catch(PDOException $e) { }
					
						print "<select name='gibbonFinanceInvoiceeID' id='gibbonFinanceInvoiceeID' style='width:302px'>" ;
							print "<option value=''></option>" ;
							while ($rowPurpose=$resultPurpose->fetch()) {
								$selected="" ;
								if ($rowPurpose["gibbonFinanceInvoiceeID"]==$gibbonFinanceInvoiceeID) {
									$selected="selected" ;
								}
								print "<option $selected value='" . $rowPurpose["gibbonFinanceInvoiceeID"] . "'>" .  formatName("", htmlPrep($rowPurpose["preferredName"]), htmlPrep($rowPurpose["surname"]), "Student", true) . "</option>" ;
							}
						print "</select>" ;
						?>
					</td>
				</tr>
				<tr>
					<td> 
						<b>Month of Issue</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<?
						print "<select name='monthOfIssue' id='monthOfIssue' style='width:302px'>" ;
							print "<option value=''></option>" ;
							for ($i=1; $i<=12; $i++) {
								$selected="" ;
								if ($monthOfIssue==$i) {
									$selected="selected" ;
								}
								print "<option $selected value=\"" . date("m",mktime(0,0,0,$i,1,0)) . "\">" . date("m",mktime(0,0,0,$i,1,0)) . " - " . date("F",mktime(0,0,0,$i,1,0)) . "</option>" ;
							}
						print "</select>" ;
						?>
					</td>
				</tr>
				<tr>
					<td> 
						<b>Billing Schedule</b><br/>
						<span style="font-size: 90%"><i></i></span>
					</td>
					<td class="right">
						<?
						try {
							$dataPurpose=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
							$sqlPurpose="SELECT * FROM gibbonFinanceBillingSchedule WHERE gibbonSchoolYearID=:gibbonSchoolYearID ORDER BY name" ;
							$resultPurpose=$connection2->prepare($sqlPurpose);
							$resultPurpose->execute($dataPurpose);
						}
						catch(PDOException $e) { }
					
						print "<select name='gibbonFinanceBillingScheduleID' id='gibbonFinanceBillingScheduleID' style='width:302px'>" ;
							print "<option value=''></option>" ;
							while ($rowPurpose=$resultPurpose->fetch()) {
								$selected="" ;
								if ($rowPurpose["gibbonFinanceBillingScheduleID"]==$gibbonFinanceBillingScheduleID) {
									$selected="selected" ;
								}
								print "<option $selected value='" . $rowPurpose["gibbonFinanceBillingScheduleID"] . "'>" .  $rowPurpose["name"] . "</option>" ;
							}
							$selected="" ;
							if ($gibbonFinanceBillingScheduleID=="Ad Hoc") {
								$selected="selected" ;
							}
							print "<option $selected value='Ad Hoc'>Ad Hoc</option>" ;
						print "</select>" ;
						?>
					</td>
				</tr>
				<?
				
				print "<tr>" ;
					print "<td class='right' colspan=2>" ;
						print "<input type='hidden' name='q' value='" . $_GET["q"] . "'>" ;
						print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Finance/invoices_manage.php'>Clear Filters</a> " ;
						print "<input type='submit' value='Go'>" ;
					print "</td>" ;
				print "</tr>" ;
			print "</table>" ;
		print "</form>" ;
		
		try {
			//Add in filter wheres
			$data=array("gibbonSchoolYearID"=>$gibbonSchoolYearID); 
			$whereSched="" ;
			$whereAdHoc="" ;
			$whereNotPending="" ;
			$today=date("Y-m-d") ;
			if ($status!="") {
				if ($status=="Pending") {
					$data["status1"]="Pending" ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1" ;
					$data["status2"]="Pending" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2" ;
					$data["status3"]="Pending" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3" ;
				}
				else if ($status=="Issued") {
					$data["status1"]="Issued" ;
					$data["dateTest1"]=$today ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1 AND gibbonFinanceInvoice.invoiceDueDate>=:dateTest1" ;
					$data["status2"]="Issued" ;
					$data["dateTest2"]=$today ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2 AND gibbonFinanceInvoice.invoiceDueDate>=:dateTest2" ;
					$data["status3"]="Issued" ;
					$data["dateTest3"]=$today ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3 AND gibbonFinanceInvoice.invoiceDueDate>=:dateTest3" ;
				}
				else if ($status=="Issued - Overdue") {
					$data["status1"]="Issued" ;
					$data["dateTest1"]=$today ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1 AND gibbonFinanceInvoice.invoiceDueDate<:dateTest1" ;
					$data["status2"]="Issued" ;
					$data["dateTest2"]=$today ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2 AND gibbonFinanceInvoice.invoiceDueDate<:dateTest2" ;
					$data["status3"]="Issued" ;
					$data["dateTest3"]=$today ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3 AND gibbonFinanceInvoice.invoiceDueDate<:dateTest3" ;
				}
				else if ($status=="Paid") {
					$data["status1"]="Paid" ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1 AND gibbonFinanceInvoice.invoiceDueDate>=paidDate" ;
					$data["status2"]="Paid" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2 AND gibbonFinanceInvoice.invoiceDueDate>=paidDate" ;
					$data["status3"]="Paid" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3 AND gibbonFinanceInvoice.invoiceDueDate>=paidDate" ;
				}
				else if ($status=="Paid - Late") {
					$data["status1"]="Paid" ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1 AND gibbonFinanceInvoice.invoiceDueDate<paidDate" ;
					$data["status2"]="Paid" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2 AND gibbonFinanceInvoice.invoiceDueDate<paidDate" ;
					$data["status3"]="Paid" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3 AND gibbonFinanceInvoice.invoiceDueDate<paidDate" ;
				}
				else if ($status=="Cancelled") {
					$data["status1"]="Cancelled" ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1" ;
					$data["status2"]="Cancelled" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2" ;
					$data["status3"]="Cancelled" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3" ;
				}
				
				else if ($status=="Refunded") {
					$data["status1"]="Refunded" ;
					$whereSched.=" AND gibbonFinanceInvoice.status=:status1" ;
					$data["status2"]="Refunded" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.status=:status2" ;
					$data["status3"]="Refunded" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.status=:status3" ;
				}
			}
			if ($gibbonFinanceInvoiceeID!="") {
				$data["gibbonFinanceInvoiceeID1"]=$gibbonFinanceInvoiceeID ;
				$whereSched.=" AND gibbonFinanceInvoice.gibbonFinanceInvoiceeID=:gibbonFinanceInvoiceeID1" ;
				$data["gibbonFinanceInvoiceeID2"]=$gibbonFinanceInvoiceeID ;
				$whereAdHoc.=" AND gibbonFinanceInvoice.gibbonFinanceInvoiceeID=:gibbonFinanceInvoiceeID2" ;
				$data["gibbonFinanceInvoiceeID3"]=$gibbonFinanceInvoiceeID ;
				$whereNotPending.=" AND gibbonFinanceInvoice.gibbonFinanceInvoiceeID=:gibbonFinanceInvoiceeID3" ;
			}
			if ($monthOfIssue!="") {
				$data["monthOfIssue1"]="%-$monthOfIssue-%" ;
				$whereSched.=" AND gibbonFinanceInvoice.invoiceIssueDate LIKE :monthOfIssue1" ;
				$data["monthOfIssue2"]="%-$monthOfIssue-%" ;
				$whereAdHoc.=" AND gibbonFinanceInvoice.invoiceIssueDate LIKE :monthOfIssue2" ;
				$data["monthOfIssue3"]="%-$monthOfIssue-%" ;
				$whereNotPending.=" AND gibbonFinanceInvoice.invoiceIssueDate LIKE :monthOfIssue3" ;
			}
			if ($gibbonFinanceBillingScheduleID!="") {
				if ($gibbonFinanceBillingScheduleID=="Ad Hoc") {
					$data["billingScheduleType1"]="Ah Hoc" ;
					$whereSched.=" AND gibbonFinanceInvoice.billingScheduleType=:billingScheduleType1" ;
					$data["billingScheduleType2"]="Ad Hoc" ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.billingScheduleType=:billingScheduleType2" ;
					$data["billingScheduleType3"]="Ad Hoc" ;
					$whereNotPending.=" AND gibbonFinanceInvoice.billingScheduleType=:billingScheduleType3" ;
				}
				else if ($gibbonFinanceBillingScheduleID!="") {
					$data["gibbonFinanceBillingScheduleID1"]=$gibbonFinanceBillingScheduleID ;
					$whereSched.=" AND gibbonFinanceInvoice.gibbonFinanceBillingScheduleID=:gibbonFinanceBillingScheduleID1" ;
					$data["gibbonFinanceBillingScheduleID2"]=$gibbonFinanceBillingScheduleID ;
					$whereAdHoc.=" AND gibbonFinanceInvoice.gibbonFinanceBillingScheduleID=:gibbonFinanceBillingScheduleID2" ;
					$data["gibbonFinanceBillingScheduleID3"]=$gibbonFinanceBillingScheduleID ;
					$whereNotPending.=" AND gibbonFinanceInvoice.gibbonFinanceBillingScheduleID=:gibbonFinanceBillingScheduleID3" ;
				}
			}
			//SQL for billing schedule AND pending
			$sql="(SELECT gibbonFinanceInvoice.gibbonFinanceInvoiceID, surname, preferredName, gibbonFinanceInvoice.invoiceTo, gibbonFinanceInvoice.status, gibbonFinanceInvoice.invoiceIssueDate, gibbonFinanceBillingSchedule.invoiceDueDate, paidDate, gibbonFinanceBillingSchedule.name AS billingSchedule, NULL AS billingScheduleExtra, notes FROM gibbonFinanceInvoice JOIN gibbonFinanceBillingSchedule ON (gibbonFinanceInvoice.gibbonFinanceBillingScheduleID=gibbonFinanceBillingSchedule.gibbonFinanceBillingScheduleID) JOIN gibbonFinanceInvoicee ON (gibbonFinanceInvoice.gibbonFinanceInvoiceeID=gibbonFinanceInvoicee.gibbonFinanceInvoiceeID) JOIN gibbonPerson ON (gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonFinanceInvoice.gibbonSchoolYearID=:gibbonSchoolYearID AND billingScheduleType='Scheduled' AND gibbonFinanceInvoice.status='Pending' $whereSched)" ; 
			$sql.=" UNION " ; 
			//SQL for Ad Hoc AND pending
			$sql.="(SELECT gibbonFinanceInvoice.gibbonFinanceInvoiceID, surname, preferredName, gibbonFinanceInvoice.invoiceTo, gibbonFinanceInvoice.status, invoiceIssueDate, invoiceDueDate, paidDate, 'Ad Hoc' AS billingSchedule, NULL AS billingScheduleExtra, notes FROM gibbonFinanceInvoice JOIN gibbonFinanceInvoicee ON (gibbonFinanceInvoice.gibbonFinanceInvoiceeID=gibbonFinanceInvoicee.gibbonFinanceInvoiceeID) JOIN gibbonPerson ON (gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonSchoolYearID=:gibbonSchoolYearID AND billingScheduleType='Ad Hoc' AND gibbonFinanceInvoice.status='Pending' $whereAdHoc)" ; 
			$sql.=" UNION " ; 
			//SQL for NOT Pending
			$sql.="(SELECT gibbonFinanceInvoice.gibbonFinanceInvoiceID, surname, preferredName, gibbonFinanceInvoice.invoiceTo, gibbonFinanceInvoice.status, gibbonFinanceInvoice.invoiceIssueDate, gibbonFinanceInvoice.invoiceDueDate, paidDate, billingScheduleType AS billingSchedule, gibbonFinanceBillingSchedule.name AS billingScheduleExtra, notes FROM gibbonFinanceInvoice LEFT JOIN gibbonFinanceBillingSchedule ON (gibbonFinanceInvoice.gibbonFinanceBillingScheduleID=gibbonFinanceBillingSchedule.gibbonFinanceBillingScheduleID) JOIN gibbonFinanceInvoicee ON (gibbonFinanceInvoice.gibbonFinanceInvoiceeID=gibbonFinanceInvoicee.gibbonFinanceInvoiceeID) JOIN gibbonPerson ON (gibbonFinanceInvoicee.gibbonPersonID=gibbonPerson.gibbonPersonID) WHERE gibbonFinanceInvoice.gibbonSchoolYearID=:gibbonSchoolYearID AND NOT gibbonFinanceInvoice.status='Pending' $whereNotPending)" ; 
			$sql.=" ORDER BY FIND_IN_SET(status, 'Pending,Issued,Paid,Refunded,Cancelled'), invoiceIssueDate, surname, preferredName" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}
		
		if ($result->rowCount()<1) {
			print "<h3>" ;
			print "View" ;
			print "</h3>" ;
			
			print "<div class='linkTop' style='text-align: right'>" ;
				print "<a style='margin-right: 3px' href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_add.php&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='New Fees & Invoices' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new_multi.gif'/></a><br/>" ;
			print "</div>" ;
			
			print "<div class='error'>" ;
			print "There are no invoices to display." ;
			print "</div>" ;
		}
		else {
			print "<h3>" ;
			print "View" ;
			if ($result->rowCount()==1) {
				print "<span style='font-weight: normal; font-style: italic; font-size: 55%'> . " . $result->rowCount() . " invoice in current view</span>" ;
			}
			if ($result->rowCount()>1) {
				print "<span style='font-weight: normal; font-style: italic; font-size: 55%'> . " . $result->rowCount() . " invoices in current view</span>" ;
			}
			print "</h3>" ;

			print "<form onsubmit='return confirm(\"Are you sure you wish to process this action? It cannot be undone.\")' method='post' action='" . $_SESSION[$guid]["absoluteURL"] . "/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_processBulk.php?gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'>" ;
				print "<fieldset style='border: none'>" ;
					print "<div class='linkTop' style='text-align: right; margin-bottom: 40px'>" ;
						print "<div style='margin: 0 0 3px 0'>" ;
							print "<a style='margin-right: 3px' href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_add.php&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='New Fees & Invoices' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_new_multi.gif'/></a><br/>" ;
						print "</div>" ;
						?>
						<input style='margin-top: 0px; float: right' type='submit' value='Go'>
						<select name="action" id="action" style='width:120px; float: right; margin-right: 1px;'>
							<option value="Select action">Select action</option>
							<?
							if ($status=="Pending") {
								print "<option value=\"delete\">Delete</option>" ;
								print "<option value=\"issue\">Issue</option>" ;
							}
							if ($status=="Issued - Overdue") {
								print "<option value=\"reminders\">Issue Reminders</option>" ;
							}
							print "<option value=\"export\">Export</option>" ;
							?>
						</select>
						<script type="text/javascript">
							var action = new LiveValidation('action');
							action.add(Validate.Exclusion, { within: ['Select action'], failureMessage: "Select something!"});
						</script>
						<?
					print "</div>" ;	
					
					print "<table cellspacing='0' style='width: 100%'>" ;
						print "<tr class='head'>" ;
							print "<th style='width: 120px'>" ;
								print "Student<br/><span style='font-style: italic; font-size: 85%'>Invoice To</span>" ;
							print "</th>" ;
							print "<th style='width: 100px'>" ;
								print "Status" ;
							print "</th>" ;
							print "<th style='width: 90px'>" ;
								print "Schedule" ;
							print "</th>" ;
							print "<th style='width: 100px'>" ;
								print "Total Value<br/><span style='font-style: italic; font-size: 85%'>" . $_SESSION[$guid]["currency"] . "</span>" ;
							print "</th>" ;
							print "<th style='width: 80px'>" ;
								print "Issue Date<br/>" ;
								print "<span style='font-style: italic; font-size: 75%'>Due Date</span>" ;
							print "</th>" ;
							print "<th style='width: 130px'>" ;
								print "Actions" ;
							print "</th>" ;
							print "<th>" ;
								?>
								<script type="text/javascript">
									$(function () { // this line makes sure this code runs on page load
										$('.checkall').click(function () {
											$(this).parents('fieldset:eq(0)').find(':checkbox').attr('checked', this.checked);
										});
									});
								</script>
								<?
								print "<input type='checkbox' class='checkall'>" ;
							print "</th>" ;
						print "</tr>" ;
			
						$count=0;
						$rowNum="odd" ;
						while ($row=$result->fetch()) {
							if ($count%2==0) {
								$rowNum="even" ;
							}
							else {
								$rowNum="odd" ;
							}
							$count++ ;
				
							//Work out extra status information
							$statusExtra="" ;
							if ($row["status"]=="Issued" AND $row["invoiceDueDate"]<date("Y-m-d")) {
								$statusExtra= "Overdue" ;
							}
							if ($row["status"]=="Paid" AND $row["invoiceDueDate"]<$row["paidDate"]) {
								$statusExtra= "Late" ;
							}
				
							//Color row by status
							if ($row["status"]=="Paid") {
								$rowNum="current" ;	
							}
							if ($row["status"]=="Issued" AND $statusExtra=="Overdue") {
								$rowNum="error" ;	
							}
				
							print "<tr class=$rowNum>" ;
								print "<td>" ;
									print "<b>" . formatName("", htmlPrep($row["preferredName"]), htmlPrep($row["surname"]), "Student", true) . "</b><br/>" ;
									print "<span style='font-style: italic; font-size: 85%'>" . $row["invoiceTo"] . "</span>" ;
								print "</td>" ;
								print "<td>" ;
									print $row["status"] ;
									if ($statusExtra!="") {
										print " - $statusExtra" ;
									}
								print "</td>" ;
								print "<td>" ;
									if ($row["billingScheduleExtra"]!="")  {
										print $row["billingScheduleExtra"] ;
									}
									else { 
										print $row["billingSchedule"] ;
									}
								print "</td>" ;
								print "<td>" ;
									//Calculate total value
									$totalFee=0 ;
									$feeError=FALSE ;
									try {
										$dataTotal=array("gibbonFinanceInvoiceID"=>$row["gibbonFinanceInvoiceID"]); 
										if ($row["status"]=="Pending") {
											$sqlTotal="SELECT gibbonFinanceInvoiceFee.fee AS fee, gibbonFinanceFee.fee AS fee2 FROM gibbonFinanceInvoiceFee LEFT JOIN gibbonFinanceFee ON (gibbonFinanceInvoiceFee.gibbonFinanceFeeID=gibbonFinanceFee.gibbonFinanceFeeID) WHERE gibbonFinanceInvoiceID=:gibbonFinanceInvoiceID" ;
										}
										else {
											$sqlTotal="SELECT gibbonFinanceInvoiceFee.fee AS fee, NULL AS fee2 FROM gibbonFinanceInvoiceFee WHERE gibbonFinanceInvoiceID=:gibbonFinanceInvoiceID" ;
										}
										$resultTotal=$connection2->prepare($sqlTotal);
										$resultTotal->execute($dataTotal);
									}
									catch(PDOException $e) { print $e->getMessage() ; print "<i>Error calculating total</i>" ; $feeError=TRUE ;}
									while ($rowTotal=$resultTotal->fetch()) {
										if (is_numeric($rowTotal["fee2"])) {
											$totalFee+=$rowTotal["fee2"] ;
										}
										else {
											$totalFee+=$rowTotal["fee"] ;
										}
									}
									if ($feeError==FALSE) {
										if (substr($_SESSION[$guid]["currency"],4)!="") {
											print substr($_SESSION[$guid]["currency"],4) . " " ;
										}
										print number_format($totalFee, 2, ".", ",") ;
									}
								print "</td>" ;
								print "<td>" ;
									if (is_null($row["invoiceIssueDate"])) {
										print "NA<br/>" ;
									}
									else {
										print dateConvertBack($row["invoiceIssueDate"]) . "<br/>" ;
									}
									print "<span style='font-style: italic; font-size: 75%'>" . dateConvertBack($row["invoiceDueDate"]) . "</span>" ;
								print "</td>" ;
								print "<td>" ;
									if ($row["status"]!="Cancelled" AND $row["status"]!="Refunded") {
										print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_edit.php&gibbonFinanceInvoiceID=" . $row["gibbonFinanceInvoiceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='Edit' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/config.png'/></a> " ;
									}
									if ($row["status"]=="Pending") {
										print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_issue.php&gibbonFinanceInvoiceID=" . $row["gibbonFinanceInvoiceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='Issue' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/page_right.png'/></a> " ;
										print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_delete.php&gibbonFinanceInvoiceID=" . $row["gibbonFinanceInvoiceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='Delete' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/garbage.png'/></a> " ;
									}
									if ($row["status"]!="Pending") {
										print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/invoices_manage_print.php&gibbonFinanceInvoiceID=" . $row["gibbonFinanceInvoiceID"] . "&gibbonSchoolYearID=$gibbonSchoolYearID&status=$status&gibbonFinanceInvoiceeID=$gibbonFinanceInvoiceeID&monthOfIssue=$monthOfIssue&gibbonFinanceBillingScheduleID=$gibbonFinanceBillingScheduleID'><img title='Print Invoices, Receipts & Reminders' src='./themes/" . $_SESSION[$guid]["gibbonThemeName"] . "/img/print.png'/></a>" ;
									}
									print "<script type='text/javascript'>" ;	
										print "$(document).ready(function(){" ;
											print "\$(\".comment-$count-$yearCount\").hide();" ;
											print "\$(\".show_hide-$count-$yearCount\").fadeIn(1000);" ;
											print "\$(\".show_hide-$count-$yearCount\").click(function(){" ;
											print "\$(\".comment-$count-$yearCount\").fadeToggle(1000);" ;
											print "});" ;
										print "});" ;
									print "</script>" ;
									if ($row["notes"]!="") {
										print "<a title='View Notes' class='show_hide-$count-$yearCount' onclick='false' href='#'><img style='margin-left: 3px' src='" . $_SESSION[$guid]["absoluteURL"] . "/themes/Default/img/page_down.png' alt='Show Comment' onclick='return false;' /></a>" ;
									}
								print "</td>" ;
								print "<td>" ;
									print "<input type='checkbox' name='gibbonFinanceInvoiceIDs[]' value='" . $row["gibbonFinanceInvoiceID"] . "'>" ;
								print "</td>" ;
							print "</tr>" ;
							if ($row["notes"]!="") {
								print "<tr class='comment-$count-$yearCount' id='comment-$count-$yearCount'>" ;
									print "<td style='border-bottom: 1px solid #333' colspan=6>" ;
										print $row["notes"] ;
									print "</td>" ;
								print "</tr>" ;
							}
						}
						print "<input type=\"hidden\" name=\"address\" value=\"" . $_SESSION[$guid]["address"] . "\">" ;
						
					print "</fieldset>" ;
				print "</table>" ;
			print "</form>" ;
		}
	}
}
?>