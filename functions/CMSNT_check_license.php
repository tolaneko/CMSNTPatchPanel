function CMSNT_check_license($licensekey, $localkey = "")
{
    global $config;
    $results = [];
    $results["status"] = "Active";
    $results["remotecheck"] = true;
    return $results;
}