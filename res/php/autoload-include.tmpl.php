<?php
if (!getenv('TYPO3_PATH_WEB')) {
    putenv('TYPO3_PATH_WEB=' . '{$web-dir}');
}
if (!getenv('TYPO3_PATH_ROOT')) {
    putenv('TYPO3_PATH_ROOT=' . '{$root-dir}');
}
if (!getenv('TYPO3_ACTIVE_FRAMEWORK_EXTENSIONS')) {
    putenv('TYPO3_ACTIVE_FRAMEWORK_EXTENSIONS=' . '{$active-typo3-extensions}');
}
