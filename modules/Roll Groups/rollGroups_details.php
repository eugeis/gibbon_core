<?php
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

@session_start() ;

//Set timezone from session variable
date_default_timezone_set($_SESSION[$guid]["timezone"]);

if (isActionAccessible($guid, $connection2, "/modules/Roll Groups/rollGroups_details.php")==FALSE) {
	//Acess denied
	print "<div class='error'>" ;
		print __($guid, "You do not have access to this action.") ;
	print "</div>" ;
}
else {
	$gibbonRollGroupID=$_GET["gibbonRollGroupID"] ;
	if ($gibbonRollGroupID=="") {
		print "<div class='error'>" ;
			print __($guid, "You have not specified one or more required parameters.") ;
		print "</div>" ;
	}
	else {
		try {
			$data=array("gibbonSchoolYearID"=>$_SESSION[$guid]["gibbonSchoolYearID"], "gibbonRollGroupID"=>$gibbonRollGroupID); 
			$sql="SELECT gibbonSchoolYear.gibbonSchoolYearID, gibbonRollGroupID, gibbonSchoolYear.name as yearName, gibbonRollGroup.name, gibbonRollGroup.nameShort, gibbonPersonIDTutor, gibbonPersonIDTutor2, gibbonPersonIDTutor3, gibbonSpace.name AS space, website FROM gibbonRollGroup JOIN gibbonSchoolYear ON (gibbonRollGroup.gibbonSchoolYearID=gibbonSchoolYear.gibbonSchoolYearID) LEFT JOIN gibbonSpace ON (gibbonRollGroup.gibbonSpaceID=gibbonSpace.gibbonSpaceID) WHERE gibbonSchoolYear.gibbonSchoolYearID=:gibbonSchoolYearID AND gibbonRollGroupID=:gibbonRollGroupID ORDER BY sequenceNumber, gibbonRollGroup.name" ; 
			$result=$connection2->prepare($sql);
			$result->execute($data);
		}
		catch(PDOException $e) { 
			print "<div class='error'>" . $e->getMessage() . "</div>" ; 
		}

		if ($result->rowCount()!=1) {
			print "<div class='error'>" ;
			print __($guid, "The selected record does not exist, or you do not have access to it.") ;
			print "</div>" ;
			print "</div>" ;
		}
		else {
			$row=$result->fetch() ;
		
			print "<div class='trail'>" ;
			print "<div class='trailHead'><a href='" . $_SESSION[$guid]["absoluteURL"] . "'>" . __($guid, "Home") . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/" . getModuleEntry($_GET["q"], $connection2, $guid) . "'>" . __($guid, getModuleName($_GET["q"])) . "</a> > <a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . getModuleName($_GET["q"]) . "/rollGroups.php'>" . __($guid, 'View Roll Groups') . "</a> > </div><div class='trailEnd'>" . $row["name"] . "</div>" ;
			print "</div>" ;
		
			print "<h3>" ;
				print __($guid, "Basic Information") ;
			print "</h3>" ;
			
			print "<table class='smallIntBorder' cellspacing='0' style='width: 100%'>" ;
				print "<tr>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>" . __($guid, 'Name') . "</span><br/>" ;
						print "<i>" . $row["name"] . "</i>" ;
					print "</td>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>" . __($guid, 'Tutors') . "</span><br/>" ;
						try {
							$dataTutor=array("gibbonPersonID1"=>$row["gibbonPersonIDTutor"], "gibbonPersonID2"=>$row["gibbonPersonIDTutor2"], "gibbonPersonID3"=>$row["gibbonPersonIDTutor3"] ); 
							$sqlTutor="SELECT gibbonPersonID, surname, preferredName, image_240 FROM gibbonPerson WHERE gibbonPersonID=:gibbonPersonID1 OR gibbonPersonID=:gibbonPersonID2 OR gibbonPersonID=:gibbonPersonID3" ;
							$resultTutor=$connection2->prepare($sqlTutor);
							$resultTutor->execute($dataTutor);
						}
						catch(PDOException $e) { 
							print "<div class='error'>" . $e->getMessage() . "</div>" ; 
						}
						$primaryTutor240="" ;
						while ($rowTutor=$resultTutor->fetch()) {
							if (isActionAccessible($guid, $connection2, "/modules/Staff/staff_view_details.php")) {
								print "<i><a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/Staff/staff_view_details.php&gibbonPersonID=" . $rowTutor["gibbonPersonID"] . "'>" . formatName("", $rowTutor["preferredName"], $rowTutor["surname"], "Staff", false, true) . "</a></i>" ;
							}
							else {
								print "<i>" . formatName("", $rowTutor["preferredName"], $rowTutor["surname"], "Staff", false, true) ;
							}
							if ($rowTutor["gibbonPersonID"]==$row["gibbonPersonIDTutor"]) {
								$primaryTutor240=$rowTutor["image_240"] ;
								if ($resultTutor->rowCount()>1) {
									print " (" . __($guid, 'Main Tutor') . ")" ;
								}
							}
							print "</i><br/>" ;
						}
					print "</td>" ;
					print "<td style='width: 33%; vertical-align: top'>" ;
						print "<span style='font-size: 115%; font-weight: bold'>" . __($guid, 'Location') . "</span><br/>" ;
						print "<i>" . $row["space"] . "</i>" ;
					print "</td>" ;
				print "</tr>" ;
				if ($row["website"]!="") {
					print "<tr>" ;
						print "<td style='width: 33%; padding-top: 15px; vertical-align: top' colspan=3>" ;
							print "<span style='font-size: 115%; font-weight: bold'>" . __($guid, 'Website') . "</span><br/>" ;
							print "<a target='_blank' href='" . $row["website"] . "'>" . $row["website"] . "</a>" ;
						print "</td>" ;
					print "</tr>" ;
				}
			print "</table>" ;
			
			print "<h2>" ;
			print __($guid, "Filters") ;
			print "</h2>" ;
	
			$orderBy=NULL ;
			if (isset($_GET["orderBy"])) {
				$orderBy=$_GET["orderBy"] ;
			}
			?>
			<form method="get" action="<?php print $_SESSION[$guid]["absoluteURL"]?>/index.php">
				<table class='noIntBorder' cellspacing='0' style="width: 100%">	
					<tr><td style="width: 30%"></td><td></td></tr>
					<tr>
						<td> 
							<b><?php print __($guid, 'Order By') ?></b><br/>
						</td>
						<td class="right">
							<select name="orderBy" id="orderBy" class="standardWidth">
								<?php
								print "<option " ; if ($orderBy=="normal") { print "selected " ; } print "value='normal'>" . __($guid, 'Roll Order') . "</option>" ;
								print "<option " ; if ($orderBy=="surname") { print "selected " ; } print "value='surname'>" . __($guid, 'Surname') . "</option>" ;
								print "<option " ; if ($orderBy=="preferredName") { print "selected " ; } print "value='preferredName'>" . __($guid, 'Preferred Name') . "</option>" ;
								?>			
							</select>
						</td>
					</tr>
					<tr>
						<td colspan=2 class="right">
							<input type="hidden" name="q" value="/modules/<?php print $_SESSION[$guid]["module"] ?>/rollGroups_details.php">
							<input type="hidden" name="gibbonRollGroupID" value="<?php print $gibbonRollGroupID ?>">
							<input type="hidden" name="address" value="<?php print $_SESSION[$guid]["address"] ?>">
							<?php
							print "<a href='" . $_SESSION[$guid]["absoluteURL"] . "/index.php?q=/modules/" . $_SESSION[$guid]["module"] . "/rollGroups_details.php&gibbonRollGroupID=$gibbonRollGroupID'>" . __($guid, 'Clear Filters') . "</a>" ;
							?>
							<input type="submit" value="<?php print __($guid, "Submit") ; ?>">
						</td>
					</tr>
				</table>
			</form>
			<?php
	
			print "<h3>" ;
				print __($guid, "Students") ;
			print "</h3>" ;
			print getRollGroupTable($guid, $gibbonRollGroupID, 5, $connection2, FALSE, $orderBy) ;
		
			//Set sidebar
			$_SESSION[$guid]["sidebarExtra"]=getUserPhoto($guid, $primaryTutor240, 240) ;
		}
	}
}
?>