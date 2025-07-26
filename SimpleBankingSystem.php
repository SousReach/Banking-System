<?php

class SimpleBankingSystem {
    private $accountsFile = 'accounts.json';
    private $currentUser = null;

    public function __construct() {
        // Create accounts file if it doesn't exist
        if (!file_exists($this->accountsFile)) {
            file_put_contents($this->accountsFile, json_encode([]));
        }
    }

    private function loadAccounts() {
        $data = file_get_contents($this->accountsFile);
        return json_decode($data, true) ?: [];
    }

    private function saveAccounts($accounts) {
        file_put_contents($this->accountsFile, json_encode($accounts, JSON_PRETTY_PRINT));
    }

    private function clearScreen() {
        system('clear'); // Use 'cls' on Windows
    }

    private function waitForEnter() {
        echo "\nPress Enter to continue...";
        fgets(STDIN);
    }

    public function createAccount() {
        $this->clearScreen();
        echo "=== CREATE NEW ACCOUNT ===\n";
        
        echo "Enter account number: ";
        $accountNumber = trim(fgets(STDIN));
        
        echo "Enter account holder name: ";
        $name = trim(fgets(STDIN));
        
        echo "Enter password: ";
        $password = trim(fgets(STDIN));
        
        echo "Enter initial deposit amount: $";
        $initialDeposit = (float)trim(fgets(STDIN));
        
        if ($initialDeposit < 0) {
            echo "Initial deposit cannot be negative!\n";
            $this->waitForEnter();
            return;
        }
        
        $accounts = $this->loadAccounts();
        
        // Check if account already exists
        if (isset($accounts[$accountNumber])) {
            echo "Account number already exists!\n";
            $this->waitForEnter();
            return;
        }
        
        // Create new account
        $accounts[$accountNumber] = [
            'name' => $name,
            'password' => password_hash($password, PASSWORD_DEFAULT),
            'balance' => $initialDeposit,
            'created_at' => date('Y-m-d H:i:s')
        ];
        
        $this->saveAccounts($accounts);
        
        echo "\nAccount created successfully!\n";
        echo "Account Number: $accountNumber\n";
        echo "Account Holder: $name\n";
        echo "Initial Balance: $" . number_format($initialDeposit, 2) . "\n";
        
        $this->waitForEnter();
    }

    public function login() {
        $this->clearScreen();
        echo "=== LOGIN ===\n";
        
        echo "Enter account number: ";
        $accountNumber = trim(fgets(STDIN));
        
        echo "Enter password: ";
        $password = trim(fgets(STDIN));
        
        $accounts = $this->loadAccounts();
        
        if (!isset($accounts[$accountNumber])) {
            echo "Account not found!\n";
            $this->waitForEnter();
            return false;
        }
        
        if (!password_verify($password, $accounts[$accountNumber]['password'])) {
            echo "Invalid password!\n";
            $this->waitForEnter();
            return false;
        }
        
        $this->currentUser = $accountNumber;
        echo "Login successful! Welcome, " . $accounts[$accountNumber]['name'] . "!\n";
        $this->waitForEnter();
        return true;
    }

    public function checkBalance() {
        if (!$this->currentUser) {
            echo "Please login first!\n";
            $this->waitForEnter();
            return;
        }
        
        $accounts = $this->loadAccounts();
        $balance = $accounts[$this->currentUser]['balance'];
        
        $this->clearScreen();
        echo "=== ACCOUNT BALANCE ===\n";
        echo "Account Number: " . $this->currentUser . "\n";
        echo "Account Holder: " . $accounts[$this->currentUser]['name'] . "\n";
        echo "Current Balance: $" . number_format($balance, 2) . "\n";
        
        $this->waitForEnter();
    }

    public function deposit() {
        if (!$this->currentUser) {
            echo "Please login first!\n";
            $this->waitForEnter();
            return;
        }
        
        $this->clearScreen();
        echo "=== DEPOSIT MONEY ===\n";
        
        $accounts = $this->loadAccounts();
        echo "Current Balance: $" . number_format($accounts[$this->currentUser]['balance'], 2) . "\n";
        
        echo "Enter deposit amount: $";
        $amount = (float)trim(fgets(STDIN));
        
        if ($amount <= 0) {
            echo "Deposit amount must be positive!\n";
            $this->waitForEnter();
            return;
        }
        
        $accounts[$this->currentUser]['balance'] += $amount;
        $this->saveAccounts($accounts);
        
        echo "\nDeposit successful!\n";
        echo "Amount Deposited: $" . number_format($amount, 2) . "\n";
        echo "New Balance: $" . number_format($accounts[$this->currentUser]['balance'], 2) . "\n";
        
        $this->waitForEnter();
    }

    public function withdraw() {
        if (!$this->currentUser) {
            echo "Please login first!\n";
            $this->waitForEnter();
            return;
        }
        
        $this->clearScreen();
        echo "=== WITHDRAW MONEY ===\n";
        
        $accounts = $this->loadAccounts();
        $currentBalance = $accounts[$this->currentUser]['balance'];
        
        echo "Current Balance: $" . number_format($currentBalance, 2) . "\n";
        
        echo "Enter withdrawal amount: $";
        $amount = (float)trim(fgets(STDIN));
        
        if ($amount <= 0) {
            echo "Withdrawal amount must be positive!\n";
            $this->waitForEnter();
            return;
        }
        
        if ($amount > $currentBalance) {
            echo "Insufficient funds! Your balance is $" . number_format($currentBalance, 2) . "\n";
            $this->waitForEnter();
            return;
        }
        
        $accounts[$this->currentUser]['balance'] -= $amount;
        $this->saveAccounts($accounts);
        
        echo "\nWithdrawal successful!\n";
        echo "Amount Withdrawn: $" . number_format($amount, 2) . "\n";
        echo "New Balance: $" . number_format($accounts[$this->currentUser]['balance'], 2) . "\n";
        
        $this->waitForEnter();
    }

    public function logout() {
        $this->currentUser = null;
        echo "Logged out successfully!\n";
        $this->waitForEnter();
    }

    public function showMainMenu() {
        $this->clearScreen();
        echo "=================================\n";
        echo "    SIMPLE BANKING SYSTEM\n";
        echo "=================================\n";
        
        if ($this->currentUser) {
            $accounts = $this->loadAccounts();
            echo "Logged in as: " . $accounts[$this->currentUser]['name'] . "\n";
            echo "Account: " . $this->currentUser . "\n";
            echo "---------------------------------\n";
            echo "1. Check Balance\n";
            echo "2. Deposit Money\n";
            echo "3. Withdraw Money\n";
            echo "4. Logout\n";
            echo "5. Exit\n";
        } else {
            echo "1. Create Account\n";
            echo "2. Login\n";
            echo "3. Exit\n";
        }
        
        echo "=================================\n";
        echo "Choose an option: ";
    }

    public function run() {
        while (true) {
            $this->showMainMenu();
            $choice = trim(fgets(STDIN));
            
            if (!$this->currentUser) {
                switch ($choice) {
                    case '1':
                        $this->createAccount();
                        break;
                    case '2':
                        $this->login();
                        break;
                    case '3':
                        echo "Thank you for using Simple Banking System!\n";
                        exit(0);
                    default:
                        echo "Invalid choice! Please try again.\n";
                        $this->waitForEnter();
                }
            } else {
                switch ($choice) {
                    case '1':
                        $this->checkBalance();
                        break;
                    case '2':
                        $this->deposit();
                        break;
                    case '3':
                        $this->withdraw();
                        break;
                    case '4':
                        $this->logout();
                        break;
                    case '5':
                        echo "Thank you for using Simple Banking System!\n";
                        exit(0);
                    default:
                        echo "Invalid choice! Please try again.\n";
                        $this->waitForEnter();
                }
            }
        }
    }
}

// Run the banking system
$bankingSystem = new SimpleBankingSystem();
$bankingSystem->run();

?>