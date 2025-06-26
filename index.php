<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>React Dashboard</title>
  <link rel="stylesheet" href="/public/css/styles.css">
  <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    .card-frame, .settings-card {
      position: absolute;
      border-radius: 12px;
      overflow: hidden;
      box-shadow: var(--shadow);
      min-width: var(--card-width);
      max-width: 500px;
      min-height: 120px;
      background: var(--bg);
    }
    .dashboard-root {
      position: relative;
      min-height: 100vh;
      background-color: var(--bg);
    }
    .draggable {
      cursor: move;
    }
  </style>
</head>
<body>
  <div id="dashboard-root" class="dashboard-root"></div>
  <script>
    const { useState, useEffect } = React;

    function Draggable({ children, style, id }) {
      const [pos, setPos] = useState({ x: 50, y: 50 });
      const ref = React.useRef();

      useEffect(() => {
        const el = ref.current;
        const onMouseDown = (e) => {
          const shiftX = e.clientX - el.getBoundingClientRect().left;
          const shiftY = e.clientY - el.getBoundingClientRect().top;
          const onMouseMove = (e) => {
            setPos({ x: e.clientX - shiftX, y: e.clientY - shiftY });
          };
          document.addEventListener('mousemove', onMouseMove);
          document.addEventListener('mouseup', () => {
            document.removeEventListener('mousemove', onMouseMove);
          }, { once: true });
        };
        el.querySelector('.draggable')?.addEventListener('mousedown', onMouseDown);
        return () => {
          el.querySelector('.draggable')?.removeEventListener('mousedown', onMouseDown);
        };
      }, []);

      return React.createElement('div', {
        ref,
        id,
        className: style.className,
        style: { ...style, left: pos.x, top: pos.y }
      }, children);
    }

    function Card({ name, visible, onClose }) {
      return visible ? (
        React.createElement(Draggable, {
          id: name,
          style: { className: 'card-frame neumorphic glow p-2' }
        },
          React.createElement('div', {
            className: 'card-header flex justify-between items-center p-1 bg-gray-800 text-white rounded-t-md draggable'
          },
            name,
            React.createElement('button', { onClick: () => onClose(name) }, '✕')
          ),
          React.createElement('iframe', {
            src: `/cards/${name}.php`,
            style: { border: 'none', width: '100%', height: '240px' }
          })
        )
      ) : null;
    }

    function SettingsCard({ activeCards, setActiveCards }) {
      return React.createElement(Draggable, {
        id: 'settings-card',
        style: { className: 'settings-card neumorphic p-3' }
      },
        React.createElement('div', {
          className: 'card-header flex justify-between items-center mb-2 bg-gray-800 text-white rounded-t-md draggable'
        },
          'Card Visibility'
        ),
        React.createElement('div', null,
          Object.keys(activeCards).map(c => (
            React.createElement('label', { key: c, style: { display: 'block' } },
              React.createElement('input', {
                type: 'checkbox',
                checked: activeCards[c],
                onChange: e => setActiveCards({ ...activeCards, [c]: e.target.checked })
              }),
              ' ', c
            )
          ))
        )
      );
    }

    function Dashboard() {
      const [cardList, setCardList] = useState([]);
      const [activeCards, setActiveCards] = useState({});
      const [loading, setLoading] = useState(true);
      
      // Fetch card list using helper PHP file
      useEffect(() => {
        fetch('/get-cards.php')
          .then(response => response.json())
          .then(cards => {
            setCardList(cards);
            // Initialize visibility state
            const initialState = {};
            cards.forEach(card => {
              initialState[card] = false;
            });
            setActiveCards(initialState);
            setLoading(false);
          })
          .catch(error => {
            console.error('Error loading cards:', error);
            setLoading(false);
          });
      }, []);
      
      useEffect(() => { feather.replace(); }, [activeCards]);

      if (loading) {
        return React.createElement('div', { className: 'loading' }, 'Loading dashboard...');
      }

      return React.createElement(React.Fragment, null,
        React.createElement(SettingsCard, { activeCards, setActiveCards }),
        cardList.map(c => (
          React.createElement(Card, {
            key: c,
            name: c,
            visible: activeCards[c],
            onClose: name => setActiveCards({ ...activeCards, [name]: false })
          })
        ))
      );
    }

    ReactDOM.render(React.createElement(Dashboard), document.getElementById('dashboard-root'));
  </script>
</body>
</html>