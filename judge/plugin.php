<?php
function plugin_compare($fin,$fout,$fans)
{
	fscanf($fout,"%ld",&$A);
	fscanf($fans,"%ld",&$B);
	if ($A==$B)
		return 1;
	if (abs($A-$B)<=1)
		return 1;
	return 0;
}
?>