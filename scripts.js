function showTab(tabName) {
    const tabs = document.querySelectorAll('.tab');
    const buttons = document.querySelectorAll('.tab-buttons div');
    tabs.forEach(tab => tab.classList.remove('active'));
    buttons.forEach(button => button.classList.remove('active'));
    document.getElementById(tabName).classList.add('active');
    document.getElementById(tabName + '-button').classList.add('active');
}
