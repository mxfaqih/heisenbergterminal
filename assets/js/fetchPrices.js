async function fetchAssetPrices() {
    const apiKey = 'MWJCTQMLHV36UA5X';
    const symbols = ['AAPL', 'GOOGL', 'MSFT', 'NVDA', 'TSLA', 'AMZN', 'FB', 'NFLX', 'BABA', 'INTC']; 
    const prices = [];

    for (const symbol of symbols) {
        const response = await fetch(`https://www.alphavantage.co/query?function=GLOBAL_QUOTE&symbol=${symbol}&apikey=${apiKey}`);
        const data = await response.json();
        const price = parseFloat(data['Global Quote']['05. price']).toFixed(2);
        const previousClose = parseFloat(data['Global Quote']['08. previous close']).toFixed(2);
        const change = parseFloat(data['Global Quote']['09. change']).toFixed(2);
        const changePercent = data['Global Quote']['10. change percent'];
        
        const direction = change >= 0 ? '▲' : '▼';
        const color = change >= 0 ? 'green' : 'red';
        
        prices.push(`
            <div class="price-box" style="color: ${color}">
                ${symbol}: $${price} ${direction} (${changePercent})
            </div>
        `);
    }

    document.getElementById('runningText').innerHTML = prices.join('');
}

// Panggil fungsi fetchAssetPrices setiap 60 detik
setInterval(fetchAssetPrices, 10000);
fetchAssetPrices();
