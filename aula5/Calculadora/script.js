const display = document.getElementById('display');
let expression = '';

const operators = ['+', '-', '*', '/'];

function updateDisplay() {
  display.textContent = expression || '0';
}

function press(val) {
  if (val === '.') {
    const lastOp = Math.max(
      expression.lastIndexOf('+'),
      expression.lastIndexOf('-'),
      expression.lastIndexOf('*'),
      expression.lastIndexOf('/')
    );
    const current = expression.slice(lastOp + 1);
    if (current.includes('.')) return;
    if (current === '' || current === '-') expression += '0.';
    else expression += '.';
  } else if (operators.includes(val)) {
    if (!expression && val !== '-') return;
    const last = expression.slice(-1);
    if (operators.includes(last)) expression = expression.slice(0, -1);
    expression += val;
  } else {
    expression += val;
  }
  updateDisplay();
}

function clearDisplay() {
  expression = '';
  display.className = 'display result-zero';
  updateDisplay();
}

function backspace() {
  expression = expression.slice(0, -1);
  updateDisplay();
}

function classify(result) {
  display.className = 'display';
  if (result > 0) display.classList.add('result-positivo');
  else if (result < 0) display.classList.add('result-negativo');
  else display.classList.add('result-zero');
}

function calculate() {
  try {
    if (!expression) return;
    // Corrige operadores finais
    while (operators.includes(expression.slice(-1))) {
      expression = expression.slice(0, -1);
    }

    const sanitized = expression.replace(/,/g, '.');
    const result = Function('return (' + sanitized + ')')();
    if (!Number.isFinite(result)) throw new Error('Erro');
    expression = String(result);
    display.textContent = result;
    classify(result);
  } catch {
    display.textContent = 'Erro';
    display.className = 'display result-zero';
    expression = '';
  }
}

// suporte teclado
document.addEventListener('keydown', (ev) => {
  const k = ev.key;
  if (/^[0-9]$/.test(k)) press(k);
  else if (k === '.' || k === ',') press('.');
  else if (operators.includes(k)) press(k);
  else if (k === 'Enter') {
    calculate();
    ev.preventDefault();
  }
  else if (k === 'Backspace') { backspace(); ev.preventDefault(); }
  else if (k === 'Escape') { clearDisplay(); ev.preventDefault(); }
});

updateDisplay();
