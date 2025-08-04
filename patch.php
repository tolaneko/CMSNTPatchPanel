<?php
// Common definitions
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Master map of function names to their Gist URLs
const FUNCTION_GIST_MAP = [
    'checkAddonLicense' => 'https://raw.githubusercontent.com/CMSNTSourceCode/CMSNTPatchPanel/refs/heads/main/functions/checkAddonLicense.php',
    'CMSNT_check_license' => 'https://raw.githubusercontent.com/CMSNTSourceCode/CMSNTPatchPanel/refs/heads/main/functions/CMSNT_check_license.php',
    'checkAddon' => 'https://raw.githubusercontent.com/CMSNTSourceCode/CMSNTPatchPanel/refs/heads/main/functions/checkAddon.php',
    'feature_enabled' => 'https://raw.githubusercontent.com/CMSNTSourceCode/CMSNTPatchPanel/refs/heads/main/functions/feature_enabled.php',
];

// Define projects with their paths, version APIs, and functions to update
$projects_config = [
    'SHOPCLONE7_ENCRYPTION' => [
        'path'=>'libs/helper.php',
        'version_api_url' => 'https://api.cmsnt.co/version.php?version=SHOPCLONE7_ENCRYPTION',
        'functions_to_update' => [
            'checkAddonLicense',
            'CMSNT_check_license'
        ],
    ],
    'SMMPANEL2_ENCRYPTION' => [
        'path'=>'libs/helper.php',
        'version_api_url' => 'https://api.cmsnt.co/version.php?version=SMMPANEL2_ENCRYPTION',
        'functions_to_update' => [
            'checkAddonLicense',
            'CMSNT_check_license'
        ]
    ],
    'SHOPCLONE6' => [
        'path'=>'libs/helper.php',
        'version_api_url' => 'https://api.cmsnt.co/version.php?version=SHOPCLONE6',
        'functions_to_update' => [
            'CMSNT_check_license',
            'checkAddon'
        ]
    ],
    'SMMPANELV1' => [
        'path'=>'../app/Helpers/Funcs.php',
        'version_api_url' => 'https://updates.baocms.net/smmpanel-v1/index.php?route=check-update&hash=e01cc7e1e3957ff1cec61d5de0b8c964&secret=e01cc7e1e3957ff1cec61d5de0b8c964',
        'functions_to_update' => [
            'CMSNT_check_license',
            'feature_enabled'
        ]
    ],
    'SHOPNICK3' => [
        'path'=>'../app/Helpers/Helper2.php',
        'version_api_url' => 'https://updates.baocms.net/shopnickv3/index.php?route=check-update&hash=e01cc7e1e3957ff1cec61d5de0b8c964&secret=e01cc7e1e3957ff1cec61d5de0b8c964',
        'functions_to_update' => [
            'CMSNT_check_license',
            'feature_enabled'
        ]
    ],
];

// List of projects that use the baocms.net API format for version data
$baocms_list = ["SMMPANELV1", "SHOPNICK3"];

// Initialize default project and version
$default_project = "UNSUPPORTED_PROJECT";
$default_version = "N/A";

// Try to read config.php content and extract values using regex
if (file_exists('config.php')) {
    $config_file_content = file_get_contents('config.php');
    if ($config_file_content !== false) {
        // Regex to find 'project' value
        if (preg_match("/'project'\s*=>\s*'(.*?)'/", $config_file_content, $matches_project)) {
            $default_project = $matches_project[1];
        }

        // Regex to find 'version' value
        if (preg_match("/'version'\s*=>\s*'(.*?)'/", $config_file_content, $matches_version)) {
            $default_version = $matches_version[1];
        }
    }
}

// Fallback for default_project if not found in config.php or unsupported
if ($default_project === "UNSUPPORTED_PROJECT") {
    if(file_exists($projects_config['SHOPNICK3']['path'] ?? '')) {
        $default_project = 'SHOPNICK3';
    } else if(file_exists($projects_config['SMMPANELV1']['path'] ?? '')) {
        $default_project = 'SMMPANELV1';
    }
}

// API logic to fetch all versions and output JSON
if (isset($_GET['action']) && $_GET['action'] === 'get_versions') {
    header('Content-Type: application/json');
    $latest_versions = [];
    foreach ($projects_config as $project_name => $details) {
        $version_api_url = $details['version_api_url'];
        $version_data = @file_get_contents($version_api_url);
        
        if (in_array($project_name, $baocms_list)) {
            $decoded_data = json_decode($version_data, true);
            $version_name = $decoded_data['data']['version_name'] ?? 'N/A';
            $version_code = $decoded_data['data']['version_code'] ?? 'N/A';
            $version_data = $version_name . ' (' . $version_code . ')';
        }
        $latest_versions[$project_name] = ($version_data !== false) ? trim($version_data) : 'N/A';
    }
    
    echo json_encode($latest_versions);
    exit;
}

// POST request (form submission)
if ($_SERVER['REQUEST_METHOD'] === 'POST' || (isset($_GET['action']) && $_GET['action'] === 'cron')) {
    header('Content-Type: application/json');

    $project_to_update = $_POST['project'] ?? $default_project;

    if (!isset($projects_config[$project_to_update])) {
        echo json_encode(['status' => 'error', 'message' => "Invalid or unsupported project."]);
        exit;
    }

    $file_path = $projects_config[$project_to_update]['path'];
    $functions_to_replace = $projects_config[$project_to_update]['functions_to_update'];

    function replace_function($code, $function_name, $new_code) {
        $pattern = '/function\s+' . preg_quote($function_name) . '\s*\(.*?\)\s*\{.*?\n\}/s';
        if (preg_match($pattern, $code)) {
            return preg_replace($pattern, $new_code, $code);
        } else {
            return "FUNCTION_NOT_FOUND: $function_name";
        }
    }

    if (!file_exists($file_path) || !is_readable($file_path)) {
        echo json_encode(['status' => 'error', 'message' => "File '$file_path' does not exist or is not readable."]);
        exit;
    }

    $code = file_get_contents($file_path);
    if ($code === false) {
        echo json_encode(['status' => 'error', 'message' => "Could not read file content for '$file_path'."]);
        exit;
    }

    $errors = [];
    foreach ($functions_to_replace as $func_name) {
        if (isset(FUNCTION_GIST_MAP[$func_name])) {
            $url = FUNCTION_GIST_MAP[$func_name];
            $new_code = @file_get_contents($url);
            if ($new_code === false) {
                $errors[] = "Could not download new code for function '$func_name' from URL '$url'.";
                continue;
            }
            $result_code = replace_function($code, $func_name, $new_code);
            if (strpos($result_code, 'FUNCTION_NOT_FOUND:') === 0) {
                $errors[] = "Function '$func_name' not found in file.";
            } else {
                $code = $result_code;
            }
        } else {
            $errors[] = "Gist URL not found for function '$func_name'. Please check FUNCTION_GIST_MAP.";
        }
    }

    if (file_put_contents($file_path, $code) === false) {
        $errors[] = "Could not write content to file '$file_path'. Please check write permissions.";
    }

    if (empty($errors)) {
        echo json_encode(['status' => 'success', 'message' => "All functions for project '$project_to_update' have been replaced successfully."]);
    } else {
        echo json_encode(['status' => 'error', 'message' => "Update completed with errors: " . implode(" ", $errors)]);
    }
    exit;
}

// Default GET request (initial page load)
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMSNT Patch Panel - @Mo_Ho_Bo</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
    <!-- Marked.js CDN for Markdown rendering -->
    <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 1rem;
            overflow-x: hidden;
        }
        .container {
            background-color: #ffffff;
            padding: 2.5rem;
            border-radius: 1rem;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            max-width: 90%;
            text-align: center;
            @media (max-width: 640px) {
                padding: 1.5rem;
            }
        }
        .form-group {
            margin-bottom: 1.5rem;
            text-align: left;
        }
        label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #374151;
        }
        input[type="text"][readonly] {
            width: 100%;
            padding: 0.75rem 1rem;
            border: 1px solid #d1d5db;
            border-radius: 0.5rem;
            background-color: #e2e8f0;
            font-size: 1rem;
            color: #4b5563;
            cursor: default;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
        }

        button {
            width: 100%;
            padding: 0.75rem 1rem;
            box-sizing: border-box;
            background: linear-gradient(to right, #6b46e5, #4f46e5);
            color: white;
            border: none;
            border-radius: 0.75rem;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease, box-shadow 0.3s ease;
            box-shadow: 0 6px 15px rgba(79, 70, 229, 0.3);
        }
        button:hover {
            background: linear-gradient(to right, #5a39c4, #4338ca);
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(79, 70, 229, 0.4);
        }
        button:active {
            background: #3730a3;
            transform: translateY(0);
            box-shadow: 0 2px 5px rgba(79, 70, 229, 0.2);
        }
        #message {
            margin-top: 1.5rem;
            padding: 1rem;
            border-radius: 0.5rem;
            font-weight: 600;
            text-align: left;
            display: none;
        }
        .message-success {
            background-color: #d1fae5;
            color: #065f46;
            border: 1px solid #34d399;
        }
        .message-error {
            background-color: #fee2e2;
            color: #991b1b;
            border: 1px solid #ef4444;
        }
        .section-separator {
            margin-top: 2.5rem;
            padding-top: 1.5rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
        }
        .section-separator h2 {
            font-size: 1.5rem;
            font-weight: 700;
            color: #374151;
            margin-bottom: 1rem;
        }
        .section-separator p {
            font-size: 0.95rem;
            color: #4b5563;
            margin-bottom: 0.5rem;
        }
        .section-separator a {
            color: #4f46e5;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.2s ease;
        }
        .section-separator a:hover {
            color: #6d28d9;
            text-decoration: underline;
        }
        .page-footer {
            margin-top: 1.5rem;
            font-size: 0.875rem;
            color: #6b7280;
            text-align: center;
        }
        /* Styling for the fetched Markdown content container */
        #additionalInfoContent {
            text-align: left;
            background-color: #f0f4f8;
            padding: 1.5rem;
            border-radius: 0.75rem;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
            border: 1px solid #d1d5db;
            line-height: 1.6;
        }
        /* Basic Markdown styling for fetched content */
        #additionalInfoContent h1, #additionalInfoContent h2, #additionalInfoContent h3 {
            font-weight: bold;
            margin-top: 1em;
            margin-bottom: 0.5em;
            color: #374151;
        }
        #additionalInfoContent h1 { font-size: 1.875rem; }
        #additionalInfoContent h2 { font-size: 1.5rem; }
        #additionalInfoContent h3 { font-size: 1.25rem; }
        #additionalInfoContent p {
            margin-bottom: 1em;
            line-height: 1.5;
        }
        #additionalInfoContent ul, #additionalInfoContent ol {
            list-style-position: outside;
            margin-left: 2.5em;
            margin-bottom: 1em;
        }
        #additionalInfoContent ul { list-style-type: disc; }
        #additionalInfoContent ol { list-style-type: decimal; }
        #additionalInfoContent a {
            color: #4f46e5;
            text-decoration: underline;
        }
        #additionalInfoContent strong { font-weight: 700; }
        #additionalInfoContent em { font-style: italic; }
        #additionalInfoContent pre {
            background-color: #e2e8f0;
            padding: 1em;
            border-radius: 0.5rem;
            overflow-x: auto;
            margin-bottom: 1em;
        }
        #additionalInfoContent code {
            font-family: monospace;
            background-color: #e2e8f0;
            padding: 0.2em 0.4em;
            border-radius: 0.25rem;
        }
        /* Spinner Styling */
        .spinner {
            border: 4px solid rgba(79, 70, 229, 0.2); /* Light purple border */
            border-top: 4px solid #4f46e5; /* Darker purple for the spinning part */
            border-radius: 50%;
            width: 1.5rem; /* Size of the spinner */
            height: 1.5rem;
            animation: spin 1s linear infinite; /* Apply the spinning animation */
            vertical-align: middle; /* Align with text */
            display: inline-block; /* Ensure it respects margin-left */
        }

        /* Keyframes for the spinning animation */
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        /* Hide by default, show with JS */
        #loadingIndicator, #versionsLoadingSpinner {
            display: none;
        }
        /* Flex container for the button and spinner */
        .button-and-spinner-container {
            display: flex;
            align-items: center;
            justify-content: center; /* Center horizontally */
            width: 100%; /* Take full width */
            margin-top: 1.5rem; /* Adjust margin as needed */
            height: 2.5rem; /* Match button height for smooth transition */
        }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="text-3xl font-bold mb-6 text-gray-800">CMSNT Patch Panel</h1>
        <p class="text-gray-600 mb-8">Configured Project:</p>

        <form id="updateForm" action="" method="POST">
            <div class="form-group">
                <label for="projectNameDisplay" class="sr-only">Project Name:</label>
                <input type="text" id="projectNameDisplay" readonly>
                <input type="hidden" id="hiddenProjectInput" name="project">
                <input type="hidden" id="hiddenVersionInput" value="<?php echo $default_version; ?>">
            </div>
            <div class="button-and-spinner-container">
                <button type="submit" id="runButton">Run</button>
                <div id="runButtonSpinner" class="spinner" style="display: none;"></div>
            </div>
        </form>

        <div id="message" class="hidden"></div>

        <div class="section-separator">
            <h2 class="text-xl font-bold mb-2 text-gray-700">Latest Versions</h2>
            <div id="versionsContainer" class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div id="versionsLoadingSpinner" class="spinner mx-auto my-4"></div>
            </div>
        </div>

        <div class="section-separator">
            <h2 class="text-xl font-bold mb-2 text-gray-700">Additional Information</h2>
            <div id="additionalInfoContent" class="text-gray-600">Loading additional information...</div>
        </div>

        <div class="section-separator contact-info">
            <h2>Contact</h2>
            <p>Email: <a href="mailto:contact@maihuybao.dev">contact@maihuybao.dev</a></p>
            <p>Telegram: <a href="https://t.me/Mo_Ho_Bo" target="_blank">@Mo_Ho_Bo</a></p>
            <p>Website: <a href="https://maihuybao.dev" target="_blank">maihuybao.dev</a></p>
        </div>
        <footer class="page-footer">
            <p>© 2025 CMSNT Patch Panel. All rights reserved.</p>
        </footer>
    </div>

    <script>
        const defaultProjectName = "<?php echo $default_project; ?>";
        const defaultProjectVersion = document.getElementById('hiddenVersionInput').value; // Get the default version
        const additionalInfoGistUrl = "https://raw.githubusercontent.com/CMSNTSourceCode/CMSNTPatchPanel/refs/heads/main/README.md";

        const projectNameDisplay = document.getElementById('projectNameDisplay');
        const hiddenProjectInput = document.getElementById('hiddenProjectInput');
        const updateForm = document.getElementById('updateForm');
        const runButton = document.getElementById('runButton');
        const runButtonSpinner = document.getElementById('runButtonSpinner');
        const messageDiv = document.getElementById('message');
        const versionsContainer = document.getElementById('versionsContainer');
        const versionsLoadingSpinner = document.getElementById('versionsLoadingSpinner');
        const additionalInfoContent = document.getElementById('additionalInfoContent');

        projectNameDisplay.value = defaultProjectName;
        hiddenProjectInput.value = defaultProjectName;

        async function displayAllVersions() {
            versionsContainer.innerHTML = '';
            versionsContainer.appendChild(versionsLoadingSpinner);
            versionsLoadingSpinner.style.display = 'block';

            try {
                const response = await fetch(window.location.href + '?action=get_versions');
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const allLatestVersions = await response.json();

                versionsContainer.innerHTML = '';
                versionsLoadingSpinner.style.display = 'none';

                if (Object.keys(allLatestVersions).length === 0) {
                    versionsContainer.innerHTML = '<p class="text-gray-600 col-span-full">No version information available.</p>';
                    return;
                }

                for (const projectName in allLatestVersions) {
                    const latestVersion = allLatestVersions[projectName];
                    const versionBox = document.createElement('div');
                    versionBox.classList.add(
                        'bg-indigo-50', 'p-3', 'rounded-lg', 'shadow-sm',
                        'flex', 'flex-col', 'items-start', 'text-left',
                        'border', 'border-indigo-200'
                    );

                    let versionText = `Version: <span class="font-bold">${latestVersion}</span>`;
                    // If this is the configured project and a version is available from config.php
                    if (projectName === defaultProjectName && defaultProjectVersion !== 'N/A') {
                        versionText = `Version: <span class="font-bold">${latestVersion}</span> (Installed: <span class="font-bold text-blue-700">${defaultProjectVersion}</span>)`;
                        // Optionally, add a class to highlight the default project's box
                        versionBox.classList.add('border-indigo-500', 'ring-2', 'ring-indigo-300');
                    }

                    versionBox.innerHTML = `
                        <p class="font-semibold text-indigo-800 text-lg">${projectName}</p>
                        <p class="text-indigo-600 text-base">${versionText}</p>
                    `;
                    versionsContainer.appendChild(versionBox);
                }
            } catch (error) {
                console.error('Error loading all versions:', error);
                versionsContainer.innerHTML = '<p class="text-red-600 col-span-full">Error loading version information. Please try again.</p>';
                versionsLoadingSpinner.style.display = 'none';
            }
        }

        async function fetchAdditionalInfo() {
            additionalInfoContent.textContent = 'Loading additional information...';
            try {
                const response = await fetch(additionalInfoGistUrl);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const markdownText = await response.text();
                additionalInfoContent.innerHTML = marked.parse(markdownText);
            } catch (error) {
                console.error('Error loading additional information:', error);
                additionalInfoContent.innerHTML = '<p class="text-red-600">Error loading additional information. Please try again.</p>';
            }
        }


        document.addEventListener('DOMContentLoaded', () => {
            displayAllVersions();
            fetchAdditionalInfo();
        });

        updateForm.addEventListener('submit', async function(event) {
            event.preventDefault();

            messageDiv.style.display = 'none';
            messageDiv.classList.remove('message-success', 'message-error');
            messageDiv.textContent = '';

            // Hide run button and show spinner
            runButton.style.display = 'none';
            runButtonSpinner.style.display = 'inline-block';

            const projectToUpdate = hiddenProjectInput.value;
            if (!projectToUpdate) {
                showMessage('No project found to update. Please check config.php', 'error');
                runButton.style.display = 'block'; // Show button again
                runButtonSpinner.style.display = 'none'; // Hide spinner
                return;
            }

            try {
                const response = await fetch(window.location.href, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `project=${encodeURIComponent(projectToUpdate)}`
                });

                const result = await response.json();

                if (response.ok) {
                    if (result.status === 'success') {
                        showMessage(result.message, 'success');
                    } else if (result.status === 'error') {
                        showMessage(result.message, 'error');
                    } else {
                        showMessage('Update complete, but response is unclear. Please check logs.', 'success');
                    }
                } else {
                    showMessage(`Server error: ${response.status} ${response.statusText}\n${result.message || ''}`, 'error');
                }

            } catch (error) {
                console.error('Error sending request:', error);
                showMessage(`Could not connect to server: ${error.message}`, 'error');
            } finally {
                // Hide spinner and show run button
                runButton.style.display = 'block';
                runButtonSpinner.style.display = 'none';
            }
        });

        function showMessage(text, type) {
            messageDiv.textContent = text;
            messageDiv.classList.add(`message-${type}`);
            messageDiv.style.display = 'block';
        }
    </script>
</body>
</html>
