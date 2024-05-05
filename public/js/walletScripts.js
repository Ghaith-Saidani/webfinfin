let selectedAccountId; 

function selectAccount(accountId, accountName, currencySymbol, balance) {
   
    document.getElementById('accountName').innerHTML = `<h3>${accountName}</h3>`;
    document.getElementById('bankNamePlaceholder').innerText = `Total Balance of ${accountName}`;
 
    document.getElementById('accountBalance').innerHTML = `<h3>${currencySymbol} ${balance.toFixed(2)}</h3>`;
    
    setAccountBalanceOverTime(accountId);



    fetch('/accounts/get-transactions/' + accountId)
        .then(response => response.json())
        .then(transactions => {
            // Update the transaction table with fetched transactions
            updateTransactionTable(transactions);
        })
        .catch(error => console.error('Error fetching transactions:', error));
}


function confirmDelete(accountId) {
    if (confirm("Are you sure you want to delete this account?")) {
        // If user confirms deletion, redirect to the delete account route
        window.location.href = '/account/delete/' + accountId;
    } else {
        // If user cancels deletion, do nothing
        return false;
    }
}

const categoryIcons = {
    'Beauty': 'bg-teal-500 fi fi-rr-barber-shop',
    'Budget': 'bg-emerald-500 fi fi-rr-bank',
    'Food': 'bg-teal-500 fi fi-rr-canned-food',
    'Bills & Fees': 'bg-blue-500 fi fi-rr-receipt',
    'Car': 'bg-cyan-500 fi fi-rr-car-side',
    'Entertainment': 'bg-cyan-500 fi fi-rr-car-side',
    'Groceries': 'bg-cyan-500 fi fi-br-basket-shopping-simple',

 
};

const defaultIcon = 'fi fi-rr-question';

function updateTransactionTable(transactions) {
    const transactionTableBody = document.querySelector('.transaction-table tbody');
    transactionTableBody.innerHTML = ''; 

    transactions.forEach(transaction => {

        const iconClass = categoryIcons[transaction.category] || defaultIcon;
        const row = `
            <tr>
            <td>
               <span class="table-category-icon">
                 <i class="${iconClass}"></i>
                 ${transaction.category}
               </span>
            </td>
                <td>${transaction.date}</td>
                <td>${transaction.description}</td>
                <td>${transaction.type}</td>
                <td>  ${transaction.currency_symbol}  ${transaction.amount.toFixed(2)}</td>     
            </tr>
        `;
        transactionTableBody.insertAdjacentHTML('beforeend', row);
    });



}





// Function to fetch transactions
// Function to fetch transactions
function fetchTransactions(page = 1) {
    // Get the selected account ID or 'all' value
    const accountId = document.getElementById('selectedAccount').value;
    console.log(accountId);
    selectedAccountId = accountId;

    let url;
    if (accountId === 'all') {
        // If 'All Accounts' option is selected, fetch all transactions
        url = `/get-transactions/all?page=${page}`;
    } else {
        // If an account ID is selected, fetch transactions for that account
        url = `/get-transactions/account/${accountId}?page=${page}`;
    }

    // Make an AJAX request to fetch transactions
    fetch(url)
        .then(response => response.json())
        .then(data => {
            // Call a function to update the transaction table with the fetched data
            updateTransactionTable2(data);
            updateDefaultAccountNamePlace(accountId);
            // Get the search term from the input field
            const searchTerm = document.getElementById('transactionSearchInput').value.trim();
            // Pass both the fetched transactions and the search term to the filterTransactions function
            filterTransactions(data, searchTerm);
            // Update pagination links
            updatePagination(data.totalPages);
        })
        .catch(error => console.error('Error:', error));
}

// Event listener for the search input field
document.getElementById('transactionSearchInput').addEventListener('input', () => fetchTransactions());

// Function to update transaction history table with fetched data
function updateTransactionTable2(transactions) {
    const transactionTableBody = document.querySelector('.transaction-table tbody');
    transactionTableBody.innerHTML = '';

    // Populate table with fetched data
    transactions.forEach(transaction => {
        const iconClass = categoryIcons[transaction.category] || defaultIcon;
        const row = `
            <tr>
                <td>
                    <span class="table-category-icon">
                        <i class="${iconClass}"></i>
                        ${transaction.category}
                    </span>
                </td>
                <td>${transaction.date}</td>
                <td>${transaction.type}</td>
                <td>${transaction.description}</td>
                <td>${transaction.currency_symbol} ${transaction.amount.toFixed(2)}</td> 
                <td>${transaction.fromaccount}</td>
                <td>${transaction.toaccount}</td>
                <td>${transaction.payee}</td>
                <td>
                    <div class="account-actions">
                        <a href="#" onclick="showDeleteConfirmationModal(${transaction.idtransaction})">
                            <span>
                                <i class="fi fi-rr-trash delete-button"></i>
                            </span>
                        </a>
                    </div>
                </td>
            </tr>
        `;
        transactionTableBody.insertAdjacentHTML('beforeend', row);
    });
}

// Function to update default account name place
function updateDefaultAccountNamePlace(accountId) {
    // Make an AJAX request to fetch account details
    fetch(`/get-account-details/${accountId}`)
        .then(response => response.json())
        .then(account => {
            // Update the default account name place with the fetched account name
            const defaultAccountNamePlace = document.getElementById('defaultAccountNamePlace');
            defaultAccountNamePlace.innerHTML = `Balance: ${account.nameaccount} <h4 class="text-success">${account.currency_symbol} ${account.balance.toFixed(2)}</h4>`;
        })
        .catch(error => console.error('Error fetching account details:', error));
}

// Function to filter transactions based on search input
function filterTransactions(transactions, searchTerm) {
    const filteredTransactions = transactions.filter(transaction => {
        // Check if any attribute of the transaction matches the search term
        return Object.values(transaction).some(value => {
            if (typeof value === 'string') {
                // For string attributes, check if the value exists and includes the search term
                return value && value.toLowerCase().includes(searchTerm.toLowerCase());
            } else if (typeof value === 'number') {
                // For numeric attributes, convert to string and check for partial match
                return value.toString().includes(searchTerm);
            }
            // For other types, ignore them in the search
            return false;
        });
    });
    updateTransactionTable2(filteredTransactions);
}

// Function to update pagination links
function updatePagination(totalPages) {
    const paginationContainer = document.querySelector('.pagination-container ul');
    paginationContainer.innerHTML = '';

    // Add previous page link
    const previousPage = `
        <li class="page-item ${currentPage === 1 ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchTransactions(${currentPage - 1})">Previous</a>
        </li>
    `;
    paginationContainer.insertAdjacentHTML('beforeend', previousPage);

    // Add page links
    for (let i = 1; i <= totalPages; i++) {
        const pageLink = `
            <li class="page-item ${currentPage === i ? 'active' : ''}">
                <a class="page-link" href="#" onclick="fetchTransactions(${i})">${i}</a>
            </li>
        `;
        paginationContainer.insertAdjacentHTML('beforeend', pageLink);
    }

    // Add next page link
    const nextPage = `
        <li class="page-item ${currentPage === totalPages ? 'disabled' : ''}">
            <a class="page-link" href="#" onclick="fetchTransactions(${currentPage + 1})">Next</a>
        </li>
    `;
    paginationContainer.insertAdjacentHTML('beforeend', nextPage);
}

// Initialize pagination
let currentPage = 1;

//search transactions





//csv

document.addEventListener('DOMContentLoaded', () => {
    // Add event listener to the Export button
    const exportButton = document.getElementById('exportButton');
    exportButton.addEventListener('click', () => {
        exportTransactionsToCSV();
    });
});

function exportTransactionsToCSV() {
    fetch('/transaction/export-transactions')
        .then(response => {
            if (!response.ok) {
                throw new Error(`Network response was not ok: ${response.status}`);
            }
            return response.blob();
        })
        .then(blob => {
            // Create a temporary anchor element
            const url = window.URL.createObjectURL(new Blob([blob]));
            console.log('Generated URL:', url);

            const a = document.createElement('a');
            a.style.display = 'none';
            a.href = url;
            a.download = 'transactions.csv';

            // Append the anchor to the body and click it programmatically
            document.body.appendChild(a);
            console.log('Anchor element created and appended to body:', a);

            a.click();
            console.log('Anchor clicked programmatically');

            // Cleanup
            window.URL.revokeObjectURL(url);
            document.body.removeChild(a);
            console.log('Cleanup done');
        })
        .catch(error => console.error('Error exporting CSV:', error));
}












//transaction modal 
function showIncomeForm(selectedAccountId) {
    // Logic to show the income transaction form
    const url = `/transaction/add/income/${selectedAccountId}`;

    // Redirect the user to the add income form page
    window.location.href = url;
}


    
    
    
    function showExpenseForm(selectedAccountId) {
        const url = `/transaction/add/expense/${selectedAccountId}`;
    
        // Redirect the user to the add income form page
        window.location.href = url;
    }
    
    function showTransferForm(selectedAccountId) {
        const url = `/transaction/add/transfer/${selectedAccountId}`;
    
        // Redirect the user to the add income form page
        window.location.href = url;
    }

    function openTransactionModal() {
      
        console.log('Selected Account ID:', selectedAccountId);
        
        // Set the account ID as a parameter when opening the modal
        const modal = new bootstrap.Modal(document.getElementById('transactionTypeModal'));
        modal.show();
        // Add the account ID as a data attribute to the modal
        document.getElementById('transactionTypeModal').setAttribute('data-account-id', selectedAccountId);
    
       
            showIncomeForm(selectedAccountId);
        
    
        
        showExpenseForm(selectedAccountId);
        
    
      
        showTransferForm(selectedAccountId);
        
    }
    function showDeleteConfirmationModal(transactionId) {
        // Set the delete URL with the transaction ID and route path
        const deleteUrl = `/transaction/delete/${transactionId}`;
    
        // Set the delete URL as a data attribute on the confirmation button
        const confirmDeleteBtn = document.getElementById('confirmDeleteBtn');
        if (confirmDeleteBtn) {
            confirmDeleteBtn.setAttribute('data-delete-url', deleteUrl);
    
            // Show the delete confirmation modal
            const modal = new bootstrap.Modal(document.getElementById('deleteConfirmationModal'));
            modal.show();
        } else {
            console.error('Confirm delete button not found.');
        }
    }
    
    function deleteTransaction() {
        // Get the delete URL from the confirmation button's data attribute
        const deleteUrl = document.getElementById('confirmDeleteBtn').getAttribute('data-delete-url');
        if (deleteUrl) {
            // Redirect to the delete URL
            window.location.href = deleteUrl;
        } else {
            console.error('Delete URL not found.');
        }
    }
    
    // Call showDeleteConfirmationModal when the delete button is clicked
    document.getElementById('confirmDeleteBtn').addEventListener('click', function() {
        const deleteUrl = this.getAttribute('data-delete-url');
        deleteTransaction(deleteUrl);
    });
    

    //payees scripts
    function loadPayees() {
        // Make an AJAX request to fetch the payees content
        fetch('/payee')
            .then(response => response.text())
            .then(html => {
                // Update the modal body with the fetched payees content
                document.getElementById('payeeModalBody').innerHTML = html;
            })
            .catch(error => {
                console.error('Error fetching payees:', error);
            });
    }

    // Call the loadPayees function when the modal is shown
    $('#payeeModal').on('show.bs.modal', function (event) {
        loadPayees();
    });

   
    function addPayee() {
        // Get the payee name from the input field
        const payeeName = document.getElementById('payeeName').value;
    
        if (!payeeName) {
            document.getElementById('warningMessage').textContent = 'Payee name cannot be empty';
            return;
        }
    
        // Send an AJAX request to add the payee
        fetch('/payee/add', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payeeName: payeeName }),
        })
        .then(response => {
            if (response.ok) {
                loadPayees();
            } else if (response.status === 400) {
                // Handle bad request (payee name already exists)
                response.text().then(errorMessage => {
                    document.getElementById('warningMessage').textContent = errorMessage;
                });
            } else {
                console.error('Failed to add payee');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    
    

    function deletePayee(payeeId) {
        // Send an AJAX request to delete the payee
        fetch(`/payee/delete/${payeeId}`, {
            method: 'DELETE',
        })
        .then(response => {
            if (response.ok) {
                // Reload the payees modal content to update the list
                loadPayees();
            } else {
                console.error('Failed to delete payee');
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    
    // Event listener for delete buttons
    document.addEventListener('click', function(event) {
        if (event.target.classList.contains('delete-payee')) {
            const payeeId = event.target.dataset.payeeId;
            if (confirm('Are you sure you want to delete this payee?')) {
                deletePayee(payeeId);
            }
        }
    });
    
    


    function editPayee(payeeId) {
        const payeeNameElement = document.getElementById(`payeeName_${payeeId}`);
        const payeeName = payeeNameElement.textContent.trim();
        const inputField = document.createElement('input');
        inputField.type = 'text';
        inputField.className = 'form-control';
        inputField.id = `editPayeeName_${payeeId}`;
        inputField.value = payeeName;
    
        const updateButton = document.createElement('button');
        updateButton.type = 'button';
        updateButton.className = 'btn btn-primary';
        updateButton.textContent = 'Update';
        updateButton.onclick = function() {
            updatePayee(payeeId);
        };
    
        const errorMessage = document.createElement('div');
        errorMessage.className = 'text-danger';
        errorMessage.id = `editWarningMessage_${payeeId}`;
    
        // Insert elements into the DOM
        const parentElement = payeeNameElement.parentElement;
        parentElement.innerHTML = '';
        parentElement.appendChild(inputField);
        parentElement.appendChild(errorMessage);
        parentElement.appendChild(updateButton);
    }
    
    function updatePayee(payeeId) {
        // Get the payee ID and new name from the input field
        const newPayeeName = document.getElementById(`editPayeeName_${payeeId}`).value;
    
        // Send an AJAX request to update the payee
        fetch(`/payee/update/${payeeId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ payeeName: newPayeeName }),
        })
        .then(response => {
            if (response.ok) {
                // Reload the payees list or update it as necessary
                loadPayees();
            } else {
                // Display error message
                response.text().then(errorMessage => {
                    const errorMessageElement = document.getElementById(`editWarningMessage_${payeeId}`);
                    errorMessageElement.textContent = errorMessage;
                });
            }
        })
        .catch(error => {
            console.error('Error:', error);
        });
    }
    

    //currency scripts 
    function getExchangeRate() {
        const fromCurrency = document.getElementById('fromCurrency').value;
        const toCurrency = document.getElementById('toCurrency').value;
        const exchangeRateInput = document.getElementById('exchangeRate');
    
        fetch(`/settings/exchange-rate/${fromCurrency}/${toCurrency}`)
            .then(response => response.json())
            .then(data => {
                if (data.exchangeRate !== undefined) {
                    exchangeRateInput.value = data.exchangeRate;
                } else {
                    exchangeRateInput.value = 'Exchange rate not found';
                }
            })
            .catch(error => {
                console.error('Error fetching exchange rate:', error);
                exchangeRateInput.value = 'Error fetching exchange rate';
            });
    }
    

    // Fetch exchange rates and update widgets
// Function to update exchange rates
function updateExchangeRates() {
    // Fetch exchange rate for USD
    fetch(`/settings/exchange-rate/TND/USD`)
        .then(response => response.json())
        .then(data => {
            const usdExchangeRateElement = document.getElementById('usdExchangeRate');
            usdExchangeRateElement.textContent = `1 TND = ${data.exchangeRate} USD`;
        })
        .catch(error => {
            console.error('Error fetching USD exchange rate:', error);
        });

    // Fetch exchange rate for Euro
    fetch(`/settings/exchange-rate/TND/EUR`)
        .then(response => response.json())
        .then(data => {
            const euroExchangeRateElement = document.getElementById('euroExchangeRate');
            euroExchangeRateElement.textContent = `1 TND = ${data.exchangeRate} Euro`;
        })
        .catch(error => {
            console.error('Error fetching Euro exchange rate:', error);
        });

    // Fetch exchange rate for Pound
    fetch(`/settings/exchange-rate/TND/GBP`)
        .then(response => response.json())
        .then(data => {
            const poundExchangeRateElement = document.getElementById('poundExchangeRate');
            poundExchangeRateElement.textContent = `1 TND = ${data.exchangeRate} GBP`;
        })
        .catch(error => {
            console.error('Error fetching Pound exchange rate:', error);
        });

    // Fetch exchange rate for Yen
    fetch(`/settings/exchange-rate/TND/JPY`)
        .then(response => response.json())
        .then(data => {
            const yenExchangeRateElement = document.getElementById('yenExchangeRate');
            yenExchangeRateElement.textContent = `1 TND = ${data.exchangeRate} JPY`;
        })
        .catch(error => {
            console.error('Error fetching Yen exchange rate:', error);
        });
}



function changeCurrency(selectedCurrency) {
// Show progress indicator before fetch
document.getElementById('progressIndicator').style.display = 'block';

    // Make an AJAX request to change the currency
    fetch(`/settings/change-currency/${selectedCurrency}`, { // Include selectedCurrency in the URL
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({}), // No need to send a body since the currency is in the URL
    })
    .then(response => response.json())
    .then(data => {
            // Hide progress indicator after fetch
            document.getElementById('progressIndicator').style.display = 'none';
        // Check if the currency change was successful
        if (data.message === 'Currency changed successfully') {
            // Reload the page or update the UI as needed
            location.reload(); // Reload the page
            // You can also update specific parts of the UI without reloading the page
        } else {
            // Handle errors or display a message to the user
            console.error('Failed to change currency:', data.error);
            // You can display an error message to the user
        }
    })
    .catch(error => {
         // Hide progress indicator on error
         document.getElementById('progressIndicator').style.display = 'none';
        console.error('Error changing currency:', error);
        // Handle errors or display a message to the user
    });
}

//stats scripts
function setAccountBalanceOverTime(accountId) {
    // Make an AJAX request to fetch balance data over time for the specified account ID
    fetch('/accounts/get-balance-over-time/' + accountId)
        .then(response => response.json())
        .then(balanceData => {
            // Process the balance data and update the chart
            updateBalanceChart(balanceData);
        })
        .catch(error => console.error('Error fetching balance over time:', error)); 
}

function updateBalanceChart(balanceData) {
    // Extract labels (dates) and balance values from the balanceData object
    const dates = Object.keys(balanceData);
    const balances = Object.values(balanceData);

    // Get the canvas element
    var ctx = document.getElementById('chartjsBalanceOvertime').getContext('2d');

    // Define the chart data
    var data = {
        labels: dates,
        datasets: [{
            label: 'Balance Over Time',
            data: balances,
            backgroundColor: "rgba(75, 192, 192, 0.5)",
            borderColor: "rgb(75, 192, 192)",
            borderWidth: 3,
            strokeColor: "rgb(75, 192, 192)",
            capBezierPoints: !0,
            pointColor: "#fff",
            pointBorderColor: "rgb(75, 192, 192)",
            pointBackgroundColor: "#FFF",
            pointBorderWidth: 3,
            pointRadius: 5,
            pointHoverBackgroundColor: "#FFF",
            pointHoverBorderColor: "rgb(75, 192, 192)",
            pointHoverRadius: 7,
            tension: 0.1
        }]
    };

    // Define the chart options
    var options = {
        responsive: true,
        maintainAspectRatio: false,
        animation: {
            animateRotate: true,
            animateScale: true,
        },
        scales: {
            x: {
                type: 'time',
                time: {
                    unit: 'month'
                },
                title: {
                    display: true,
                    text: 'Date'
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'Balance'
                }
            }
        }
    };

    // Create or update the chart instance
    if (window.myChart) {
        // Update existing chart
        window.myChart.data = data;
        window.myChart.options = options;
        window.myChart.update();
    } else {
        // Create new chart
        window.myChart = new Chart(ctx, {
            type: 'line',
            data: data,
            options: options
        });
    }
}

//incomevexpense

// JavaScript function to fetch income vs expense data and update the chart
