<?php
class IntegrationConfig {
    // Ganti dengan URL Web App dari Google Apps Script Anda setelah di-deploy
    public const GOOGLE_SHEETS_WEBHOOK_URL = 'https://script.google.com/macros/s/AKfycbwyOjlbRBOrnK_b1IXZbCEI9r8oOKWNMnBmNhOSfywi0UQTWej7rRCyyN8melFLcwtrvA/exec';
    
    // Ganti dengan URL Google Spreadsheet Anda agar bisa diakses langsung dari Dashboard Admin
    public const GOOGLE_SHEETS_SPREADSHEET_URL = 'https://docs.google.com/spreadsheets/d/1HbhjqvCk1DTX9uc9srYS7cpST4gNZG6kA07iKz48gJ0/edit?gid=685774435#gid=685774435'; 
    
    // Kunci rahasia sederhana untuk validasi antara PHP dan GAS
    public const API_KEY = 'KUNCI_RAHASIA_ANDA';
}
