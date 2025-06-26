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
    :root {
      --primary: #4f46e5;
      --secondary: #64748b;
      --dark: #1e293b;
      --light: #ffffff;
    }
    
    .settings-button {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--primary);
      color: white;
      border: none;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      z-index: 100;
      transition: all 0.3s ease;
    }
    
    .settings-button:hover {
      transform: rotate(30deg);
      background: #4338ca;
    }
    
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.5);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
    }
    
    .modal {
      background: var(--light);
      border-radius: 12px;
      padding: 24px;
      width: 90%;
      max-width: 500px;
      box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 20px;
    }
    
    .modal-header h2 {
      margin: 0;
      color: var(--dark);
    }
    
    .modal-close {
      background: none;
      border: none;
      font-size: 24px;
      cursor: pointer;
      color: var(--secondary);
    }
    
    .modal-content {
      margin-bottom: 24px;
    }
    
    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
    }
    
    .modal-footer button {
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
    }
    
    .modal-footer .save-btn {
      background: var(--primary);
      color: white;
      border: none;
    }
    
    .modal-footer .cancel-btn {
      background: transparent;
      border: 1px solid var(--secondary);
      color: var(--secondary);
    }
    
    .settings-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 16px;
      flex-wrap: wrap;
    }
    
    .settings-actions button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 8px 16px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
      transition: all 0.2s;
    }
    
    .settings-actions button.hide-all {
      background: #ef4444;
    }
    
    .settings-actions button.arrange {
      background: #10b981;
    }
    
    .visibility-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(140px, 1fr));
      gap: 8px;
    }
    
    .visibility-item {
      display: flex;
      align-items: center;
      gap: 6px;
    }
    
    .visibility-item label {
      cursor: pointer;
      font-size: 14px;
    }
    
    .loading {
      display: flex;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-size: 18px;
      color: var(--secondary);
    }
    
    .empty-dashboard {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      text-align: center;
      padding: 20px;
      color: var(--secondary);
    }
    
    .empty-dashboard h3 {
      margin-bottom: 10px;
      color: var(--dark);
    }
    
    .empty-dashboard p {
      max-width: 500px;
      margin-bottom: 20px;
    }
    
    .empty-dashboard button {
      background: var(--primary);
      color: white;
      border: none;
      padding: 10px 20px;
      border-radius: 6px;
      cursor: pointer;
      font-weight: 500;
    }
  </style>
</head>
<body>
  <div id="dashboard-root" class="dashboard-root"></div>
  <script>
    const { useState, useEffect } = React;

    // Original draggable implementation (restored)
    function Draggable({ children, style, id }) {
      const [pos, setPos] = useState({ x: 50, y: 50 });
      const ref = React.useRef();

      useEffect(() => {
        const el = ref.current;
        const onMouseDown = (e) => {
          // Only start drag if the target is draggable
          if (!e.target.closest('.draggable')) return;
          
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
        
        el.addEventListener('mousedown', onMouseDown);
        return () => {
          el.removeEventListener('mousedown', onMouseDown);
        };
      }, []);

      return React.createElement('div', {
        ref,
        id,
        className: style.className,
        style: { ...style, left: pos.x, top: pos.y, position: 'absolute' }
      }, children);
    }

    function Card({ name, visible, onClose }) {
      if (!visible) return null;
      
      return React.createElement(Draggable, {
        id: name,
        style: { className: 'card-frame neumorphic glow p-2' }
      },
        React.createElement('div', {
          className: 'card-header flex justify-between items-center p-1 bg-gray-800 text-white rounded-t-md draggable'
        },
          name,
          React.createElement('button', { 
            onClick: (e) => {
              e.stopPropagation();
              onClose(name);
            },
            title: 'Close card'
          }, '✕')
        ),
        React.createElement('iframe', {
          src: `/cards/${name}.php`,
          style: { border: 'none', width: '100%', height: '240px' }
        })
      );
    }

    function SettingsCard({ activeCards, setActiveCards, onShowAll, onHideAll, onArrangeCards }) {
      return React.createElement(Draggable, {
        id: 'settings-card',
        style: { className: 'settings-card neumorphic p-3' }
      },
        React.createElement('div', {
          className: 'card-header flex justify-between items-center mb-2 bg-gray-800 text-white rounded-t-md draggable'
        },
          'Card Visibility Settings'
        ),
        React.createElement('div', { className: 'p-3' },
          React.createElement('div', { className: 'settings-actions' },
            React.createElement('button', { 
              onClick: onShowAll,
              className: 'show-all'
            }, 'Show All'),
            React.createElement('button', { 
              onClick: onHideAll,
              className: 'hide-all'
            }, 'Hide All'),
            React.createElement('button', { 
              onClick: onArrangeCards,
              className: 'arrange'
            }, 'Arrange Cards')
          ),
          React.createElement('div', { className: 'visibility-list' },
            Object.keys(activeCards).map(card => (
              React.createElement('div', { 
                key: card, 
                className: 'visibility-item'
              },
                React.createElement('input', {
                  type: 'checkbox',
                  id: `vis-${card}`,
                  checked: activeCards[card],
                  onChange: e => setActiveCards({ ...activeCards, [card]: e.target.checked })
                }),
                React.createElement('label', { 
                  htmlFor: `vis-${card}`,
                  title: card
                }, card.length > 15 ? `${card.substring(0, 12)}...` : card)
              )
            ))
          )
        )
      );
    }

    function DashboardSettingsModal({ isOpen, onClose }) {
      if (!isOpen) return null;
      
      return React.createElement('div', { className: 'modal-overlay' },
        React.createElement('div', { className: 'modal' },
          React.createElement('div', { className: 'modal-header' },
            React.createElement('h2', null, 'Dashboard Settings'),
            React.createElement('button', { 
              className: 'modal-close',
              onClick: onClose,
              title: 'Close settings'
            }, '✕')
          ),
          React.createElement('div', { className: 'modal-content' },
            React.createElement('p', null, 'Dashboard settings panel will be implemented in future updates.'),
            React.createElement('p', null, 'You can configure additional dashboard preferences here.')
          ),
          React.createElement('div', { className: 'modal-footer' },
            React.createElement('button', { 
              className: 'cancel-btn',
              onClick: onClose
            }, 'Close')
          )
        )
      );
    }

    function Dashboard() {
      const [cardList, setCardList] = useState([]);
      const [activeCards, setActiveCards] = useState({});
      const [loading, setLoading] = useState(true);
      const [showSettingsModal, setShowSettingsModal] = useState(false);
      
      // Fetch card list using helper PHP file
      useEffect(() => {
        fetch('/get-cards.php')
          .then(response => response.json())
          .then(cards => {
            setCardList(cards);
            // Initialize visibility state
            const initialState = {};
            cards.forEach(card => {
              initialState[card] = true; // Show all by default
            });
            setActiveCards(initialState);
            setLoading(false);
          })
          .catch(error => {
            console.error('Error loading cards:', error);
            setLoading(false);
          });
      }, []);
      
      useEffect(() => { 
        if (typeof feather !== 'undefined') {
          feather.replace(); 
        }
      }, [activeCards]);
      
      const handleShowAll = () => {
        const newState = {};
        cardList.forEach(card => newState[card] = true);
        setActiveCards(newState);
      };
      
      const handleHideAll = () => {
        const newState = {};
        cardList.forEach(card => newState[card] = false);
        setActiveCards(newState);
      };
      
      const handleArrangeCards = () => {
        // This is just a visual demo - in a real app we would need to
        // store positions and update them, but since positions are local
        // to each Draggable, we can't directly control them
        alert('Cards arranged to center! In a full implementation, this would position all cards in a grid layout.');
      };
      
      const handleCardClose = (cardName) => {
        setActiveCards(prev => ({
          ...prev,
          [cardName]: false
        }));
      };
      
      const toggleSettingsModal = () => {
        setShowSettingsModal(prev => !prev);
      };
      
      const visibleCards = cardList.filter(card => activeCards[card]);
      
      if (loading) {
        return React.createElement('div', { className: 'loading' }, 
          React.createElement('div', null, 'Loading dashboard...'),
          React.createElement('div', { 
            style: { 
              width: '40px', 
              height: '40px', 
              border: '4px solid #f3f3f3',
              borderTop: '4px solid #4f46e5',
              borderRadius: '50%',
              margin: '20px auto',
              animation: 'spin 1s linear infinite'
            }
          })
        );
      }
      
      if (cardList.length === 0) {
        return React.createElement('div', { className: 'empty-dashboard' },
          React.createElement('h3', null, 'No Cards Available'),
          React.createElement('p', null, 'Add card PHP files to your /cards/ directory to see them appear here.'),
          React.createElement('button', { onClick: () => window.location.reload() }, 'Refresh')
        );
      }
      
      return React.createElement(React.Fragment, null,
        React.createElement('button', { 
          className: 'settings-button',
          onClick: toggleSettingsModal,
          title: 'Dashboard settings'
        },
          React.createElement('i', { 'data-feather': 'settings' })
        ),
        
        React.createElement(SettingsCard, {
          activeCards,
          setActiveCards,
          onShowAll: handleShowAll,
          onHideAll: handleHideAll,
          onArrangeCards: handleArrangeCards
        }),
        
        cardList.map(card => (
          React.createElement(Card, {
            key: card,
            name: card,
            visible: activeCards[card],
            onClose: handleCardClose
          })
        )),
        
        visibleCards.length === 0 && React.createElement('div', { className: 'empty-dashboard' },
          React.createElement('h3', null, 'Dashboard is Empty'),
          React.createElement('p', null, 'Enable cards using the Card Visibility Settings panel'),
          React.createElement('button', { onClick: handleShowAll }, 'Show All Cards')
        ),
        
        React.createElement(DashboardSettingsModal, {
          isOpen: showSettingsModal,
          onClose: toggleSettingsModal
        }),
        
        // Add CSS animation for loading spinner
        React.createElement('style', null, `
          @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
          }
        `)
      );
    }

    ReactDOM.render(React.createElement(Dashboard), document.getElementById('dashboard-root'));
  </script>
</body>
</html>