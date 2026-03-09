const messageBox = document.getElementById('messagebox');
const userInput = document.getElementById('userinput');

const OPENROUTER_API_KEY = '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';

let conversationMessages = [
    { role: 'system', content: 'You are Emma, the AI assistant of Kimjay & Madel Meat Stall.' },
    { role: 'system', content: 'Help customers with meat products, prices, orders, delivery, and freshness only.' },
    { role: 'system', content: 'Focus only on fresh meats such as chicken, pork, and beef.' },
    { role: 'system', content: 'Kimjay & Madel Meat Stall offers clean, fresh, and quality meat for daily cooking.' },
    { role: 'system', content: 'Customers can order meat online for fast and reliable delivery.' },
    { role: 'system', content: 'Bulk orders and preferred meat cuts are available.' },
    { role: 'system', content: 'Keep answers short, friendly, and helpful.' },
    { role: 'system', content: 'Do not provide programming or technical information.' },
    { role: 'system', content: 'If asked for private or confidential information, reply: I’m sorry, but I cannot help with that request.' },
    { role: 'system', content: 'If asked about unavailable products, politely suggest checking other meats.' }
];

function appendMessage(role, content, isTyping = false) {
    const messageDiv = document.createElement('div');
    messageDiv.className = `message ${role} ${isTyping ? 'typing' : ''}`;
    messageDiv.textContent = content;
    messageBox.appendChild(messageDiv);
    messageBox.scrollTop = messageBox.scrollHeight;
    return messageDiv;
}

async function handleProductQuery(userMessage) {
    const match = userMessage.match(/(?:price|show|how much|cost)\s+(.+)/i);
    if (match) {
        const productQuery = match[1].trim().toLowerCase();

        if (productQuery.includes("chicken")) {
            appendMessage('bot', `Chicken - ₱180 to ₱220 per kilogram. Fresh, locally sourced chicken, cleaned and prepared with care. Perfect for frying, roasting, soups, and daily meals. Available in whole or cut options.`);
            return true;
        }

        const res = await fetch(`emma-product-search.php?q=${encodeURIComponent(productQuery)}`);
        const data = await res.json();
        if (data.success) {
            appendProductMessage(data.product);
        } else {
            appendMessage('bot', `Sorry, we couldn't find "${productQuery}" in our available meats.`);
        }
        return true;
    }
    return false;
}

function appendProductMessage(product) {
    const messageDiv = document.createElement('div');
    messageDiv.className = 'message bot';
    messageDiv.innerHTML = `
        <div style="display:flex;flex-direction:column;align-items:center;gap:4px;">
            <img src="${product.img}" alt="${product.name}" style="width:256px;height:256px;object-fit:cover;border-radius:6px;margin-bottom:2px;">
            <div style="font-weight:bold;font-size:1.1em;text-align:center;">${product.name}</div>
            <div style="color:#569c71;font-size:1.1em;text-align:center;">₱${parseFloat(product.price).toFixed(2)}</div>
        </div>
    `;
    messageBox.appendChild(messageDiv);
    messageBox.scrollTop = messageBox.scrollHeight;
}

async function sendMessage() {
    const message = userInput.value.trim();
    if (!message) return;

    appendMessage('user', message);
    userInput.value = '';

    if (await handleProductQuery(message)) {
        return;
    }

    conversationMessages.push({ role: 'user', content: message });

    const typingIndicator = appendMessage('bot', 'Preparing your request...', true);

    try {
        const response = await fetch('https://openrouter.ai/api/v1/chat/completions', {
            method: 'POST',
            headers: {
                'Authorization': `Bearer ${OPENROUTER_API_KEY}`,
                'Content-Type': 'application/json',
                'HTTP-Referer': window.location.origin,
                'X-Title': 'Kimjay & Madel Meat Stall'
            },
            body: JSON.stringify({
                model: 'nvidia/nemotron-3-nano-30b-a3b:free',
                messages: conversationMessages,
                reasoning: { enabled: true }
            })
        });

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const data = await response.json();
        const assistantMessage = data.choices?.[0]?.message;

        if (assistantMessage) {
            conversationMessages.push(assistantMessage);
        }

        const botReply = assistantMessage?.content || 'How can I help you with your meat order today?';

        typingIndicator.classList.remove('typing');
        typingIndicator.textContent = botReply;
    } catch (error) {
        typingIndicator.classList.remove('typing');
        typingIndicator.textContent = `Error: ${error.message}`;
    }
}

userInput.addEventListener('keypress', (event) => {
    if (event.key === 'Enter') {
        sendMessage();
    }
});
