<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Neon Glass Dashboard</title>
  <script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }
    
    :root {
      --bg: #0f0e17;
      --glass: rgba(30, 30, 45, 0.7);
      --cyan: #00f7ff;
      --magenta: #ff00d6;
      --yellow: #ffed00;
      --primary: #4f46e5;
      --dark: #1e293b;
      --light: #ffffff;
      --shadow: 0 10px 30px rgba(0, 0, 0, 0.5);
      --card-width: 320px;
      --card-height: 300px;
      --neon-cyan: 0 0 10px rgba(0, 247, 255, 0.7);
      --neon-magenta: 0 0 10px rgba(255, 0, 214, 0.7);
      --neon-yellow: 0 0 10px rgba(255, 237, 0, 0.7);
      --transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
    }
    
    body {
      margin: 0;
      padding: 0;
      font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
      background-color: var(--bg);
      color: #e2e2e2;
      min-height: 100vh;
      overflow-x: hidden;
      background-image: 
        radial-gradient(circle at 10% 20%, rgba(0, 247, 255, 0.05) 0%, transparent 20%),
        radial-gradient(circle at 90% 80%, rgba(255, 0, 214, 0.05) 0%, transparent 20%),
        radial-gradient(circle at 50% 50%, rgba(255, 237, 0, 0.05) 0%, transparent 30%);
    }
    
    .card-frame, .settings-card {
      position: absolute;
      border-radius: 16px;
      overflow: hidden;
      box-shadow: var(--shadow);
      min-width: var(--card-width);
      max-width: 500px;
      min-height: 120px;
      background: var(--glass);
      backdrop-filter: blur(12px);
      border: 1px solid rgba(255, 255, 255, 0.1);
      z-index: 10;
      transition: var(--transition);
    }
    
    .card-frame:hover, .settings-card:hover {
      transform: translateY(-5px);
      box-shadow: var(--shadow), var(--neon-cyan);
    }
    
    .dashboard-root {
      position: relative;
      min-height: 100vh;
      background-color: var(--bg);
      padding: 20px;
    }
    
    .draggable {
      cursor: move;
    }
    
    .card-header {
      background: linear-gradient(90deg, rgba(0,0,0,0.3), rgba(79, 70, 229, 0.3));
      color: white;
      padding: 12px 20px;
      font-weight: 600;
      display: flex;
      justify-content: space-between;
      align-items: center;
      user-select: none;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
      text-shadow: 0 0 5px rgba(255, 255, 255, 0.5);
    }
    
    .card-header button {
      background: transparent;
      border: none;
      color: white;
      cursor: pointer;
      font-size: 18px;
      padding: 4px 8px;
      border-radius: 4px;
      transition: var(--transition);
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
    }
    
    .card-header button:hover {
      background: rgba(255, 255, 255, 0.2);
      box-shadow: var(--neon-magenta);
    }
    
    .settings-card .card-header {
      background: linear-gradient(90deg, rgba(0,0,0,0.3), rgba(100, 116, 139, 0.3));
    }
    
    .settings-content {
      padding: 20px;
      background: rgba(0, 0, 0, 0.2);
    }
    
    .settings-actions {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    
    .settings-actions button {
      background: rgba(0, 0, 0, 0.4);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.1);
      padding: 10px 15px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: var(--transition);
      flex: 1;
      min-width: 120px;
      position: relative;
      overflow: hidden;
    }
    
    .settings-actions button::before {
      content: '';
      position: absolute;
      top: 0;
      left: -100%;
      width: 100%;
      height: 100%;
      background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
      transition: 0.5s;
    }
    
    .settings-actions button:hover::before {
      left: 100%;
    }
    
    .settings-actions button:hover {
      border-color: var(--cyan);
      box-shadow: var(--neon-cyan);
    }
    
    .settings-actions button.show-all:hover {
      border-color: var(--yellow);
      box-shadow: var(--neon-yellow);
    }
    
    .settings-actions button.hide-all:hover {
      border-color: var(--magenta);
      box-shadow: var(--neon-magenta);
    }
    
    .settings-actions button.arrange:hover {
      border-color: var(--cyan);
      box-shadow: var(--neon-cyan);
    }
    
    .visibility-list {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(160px, 1fr));
      gap: 12px;
    }
    
    .visibility-item {
      display: flex;
      align-items: center;
      gap: 8px;
      padding: 8px;
      border-radius: 8px;
      background: rgba(0, 0, 0, 0.2);
      transition: var(--transition);
    }
    
    .visibility-item:hover {
      background: rgba(79, 70, 229, 0.2);
      box-shadow: var(--neon-cyan);
    }
    
    .visibility-item label {
      cursor: pointer;
      font-size: 14px;
      flex: 1;
    }
    
    .settings-button {
      position: fixed;
      top: 20px;
      right: 20px;
      background: var(--glass);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.1);
      width: 50px;
      height: 50px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      box-shadow: var(--shadow);
      z-index: 100;
      transition: var(--transition);
      backdrop-filter: blur(10px);
    }
    
    .settings-button:hover {
      transform: rotate(30deg);
      border-color: var(--yellow);
      box-shadow: var(--neon-yellow);
    }
    
    .modal-overlay {
      position: fixed;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background: rgba(0, 0, 0, 0.7);
      display: flex;
      align-items: center;
      justify-content: center;
      z-index: 1000;
      backdrop-filter: blur(5px);
    }
    
    .modal {
      background: var(--glass);
      border-radius: 20px;
      padding: 30px;
      width: 90%;
      max-width: 500px;
      box-shadow: var(--shadow), var(--neon-magenta);
      border: 1px solid rgba(255, 255, 255, 0.1);
      backdrop-filter: blur(20px);
    }
    
    .modal-header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 25px;
      padding-bottom: 15px;
      border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .modal-header h2 {
      margin: 0;
      color: white;
      font-size: 24px;
      text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
    }
    
    .modal-close {
      background: transparent;
      border: none;
      font-size: 28px;
      cursor: pointer;
      color: white;
      width: 40px;
      height: 40px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      transition: var(--transition);
    }
    
    .modal-close:hover {
      background: rgba(255, 255, 255, 0.1);
      box-shadow: var(--neon-magenta);
    }
    
    .modal-content {
      margin-bottom: 25px;
      line-height: 1.6;
    }
    
    .modal-content p {
      margin-bottom: 15px;
    }
    
    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 15px;
    }
    
    .modal-footer button {
      padding: 10px 20px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      transition: var(--transition);
      background: rgba(0, 0, 0, 0.3);
      color: white;
      border: 1px solid rgba(255, 255, 255, 0.1);
    }
    
    .modal-footer button:hover {
      border-color: var(--yellow);
      box-shadow: var(--neon-yellow);
    }
    
    .loading {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 100vh;
      font-size: 20px;
      color: var(--cyan);
      text-shadow: var(--neon-cyan);
    }
    
    .empty-dashboard {
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      height: 80vh;
      text-align: center;
      padding: 30px;
      color: #aaa;
    }
    
    .empty-dashboard h3 {
      margin-bottom: 20px;
      color: white;
      font-size: 28px;
      text-shadow: var(--neon-cyan);
    }
    
    .empty-dashboard p {
      max-width: 500px;
      margin-bottom: 30px;
      font-size: 18px;
    }
    
    .empty-dashboard button {
      background: linear-gradient(45deg, #4f46e5, #8b5cf6);
      color: white;
      border: none;
      padding: 12px 30px;
      border-radius: 8px;
      cursor: pointer;
      font-weight: 500;
      font-size: 16px;
      transition: var(--transition);
      box-shadow: 0 5px 15px rgba(79, 70, 229, 0.4);
    }
    
    .empty-dashboard button:hover {
      transform: translateY(-3px);
      box-shadow: 0 8px 20px rgba(79, 70, 229, 0.6), var(--neon-cyan);
    }
    
    .card-iframe {
      width: 100%;
      height: 240px;
      border: none;
      background: rgba(0, 0, 0, 0.2);
    }
    
    .grid-layout {
      display: grid;
      grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
      gap: 25px;
      padding: 20px;
    }
    
    @keyframes float {
      0% { transform: translateY(0px); }
      50% { transform: translateY(-10px); }
      100% { transform: translateY(0px); }
    }
    
    @keyframes glow {
      0% { box-shadow: var(--shadow), var(--neon-cyan); }
      33% { box-shadow: var(--shadow), var(--neon-magenta); }
      66% { box-shadow: var(--shadow), var(--neon-yellow); }
      100% { box-shadow: var(--shadow), var(--neon-cyan); }
    }
    
    .card-frame {
      animation: float 6s ease-in-out infinite;
    }
    
    .settings-card {
      animation: glow 8s infinite linear;
    }
  </style>
</head>
<body>
  <div id="dashboard-root" class="dashboard-root"></div>
  <script>
    const { useState, useEffect, useRef } = React;

    function Draggable({ children, style, id, position, onPositionChange }) {
      const [isDragging, setIsDragging] = useState(false);
      const ref = useRef(null);
      const dragOffset = useRef({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        if (e.target.closest('button')) return;
        if (!e.target.closest('.draggable')) return;
        
        const rect = ref.current.getBoundingClientRect();
        dragOffset.current = {
          x: e.clientX - rect.left,
          y: e.clientY - rect.top
        };
        
        setIsDragging(true);
        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
      };

      const handleMouseMove = (e) => {
        if (!isDragging) return;
        
        const newX = e.clientX - dragOffset.current.x;
        const newY = e.clientY - dragOffset.current.y;
        
        onPositionChange(id, { x: newX, y: newY });
      };

      const handleMouseUp = () => {
        setIsDragging(false);
        document.removeEventListener('mousemove', handleMouseMove);
        document.removeEventListener('mouseup', handleMouseUp);
      };

      useEffect(() => {
        return () => {
          document.removeEventListener('mousemove', handleMouseMove);
          document.removeEventListener('mouseup', handleMouseUp);
        };
      }, []);

      return React.createElement('div', {
        ref,
        id,
        className: `${style.className} ${isDragging ? 'dragging' : ''}`,
        style: { 
          ...style, 
          left: `${position.x}px`, 
          top: `${position.y}px`,
          cursor: isDragging ? 'grabbing' : 'grab'
        },
        onMouseDown: handleMouseDown
      }, children);
    }

    function Card({ name, visible, onClose, position, onPositionChange }) {
      if (!visible) return null;
      
      return React.createElement(Draggable, {
        id: name,
        style: { className: 'card-frame' },
        position: position,
        onPositionChange: onPositionChange
      },
        React.createElement('div', {
          className: 'card-header draggable'
        },
          name,
          React.createElement('button', { 
            onClick: () => onClose(name),
            title: 'Close card'
          }, '✕')
        ),
        React.createElement('iframe', {
          className: 'card-iframe',
          src: `/cards/${name}.php`,
          style: { 
            border: 'none', 
            width: '100%', 
            height: '240px',
            display: 'block'
          }
        })
      );
    }

    function SettingsCard({ activeCards, setActiveCards, onShowAll, onHideAll, onArrangeCards }) {
      const position = { x: 50, y: 50 };
      
      return React.createElement(Draggable, {
        id: 'settings-card',
        style: { className: 'settings-card' },
        position: position,
        onPositionChange: () => {} // Settings card position is fixed
      },
        React.createElement('div', {
          className: 'card-header draggable'
        },
          'Card Controls'
        ),
        React.createElement('div', { className: 'settings-content' },
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
      const [cardPositions, setCardPositions] = useState({});
      const [loading, setLoading] = useState(true);
      const [showSettingsModal, setShowSettingsModal] = useState(false);
      
      // Simulate fetching card list (in real app, use fetch('/get-cards.php'))
      useEffect(() => {
        setTimeout(() => {
          const mockCards = [
            "SalesOverview", "RevenueChart", "UserActivity", 
            "PerformanceMetrics", "RecentOrders", "TaskProgress",
            "ServerStatus", "Notifications", "CalendarEvents"
          ];
          setCardList(mockCards);
          
          // Initialize visibility state and positions
          const initialState = {};
          const initialPositions = {};
          mockCards.forEach((card, index) => {
            initialState[card] = true;
            initialPositions[card] = { 
              x: 50 + (index % 3) * 340, 
              y: 100 + Math.floor(index / 3) * 320 
            };
          });
          
          setActiveCards(initialState);
          setCardPositions(initialPositions);
          setLoading(false);
        }, 800);
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
        const newPositions = {};
        const cols = Math.min(3, Math.ceil(Math.sqrt(cardList.length)));
        const cardWidth = 340;
        const cardHeight = 320;
        
        const startX = 50;
        const startY = 100;
        
        cardList.forEach((card, index) => {
          const row = Math.floor(index / cols);
          const col = index % cols;
          
          newPositions[card] = {
            x: startX + col * cardWidth,
            y: startY + row * cardHeight
          };
        });
        
        setCardPositions(newPositions);
      };
      
      const handlePositionChange = (cardId, newPosition) => {
        setCardPositions(prev => ({
          ...prev,
          [cardId]: newPosition
        }));
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
          React.createElement('div', { style: { 
            fontSize: '24px', 
            marginBottom: '20px',
            textShadow: '0 0 10px #00f7ff'
          }}, 'Loading Cyber Dashboard...'),
          React.createElement('div', { 
            style: { 
              width: '60px', 
              height: '60px', 
              border: '5px solid rgba(0, 247, 255, 0.2)',
              borderTop: '5px solid #00f7ff',
              borderRadius: '50%',
              margin: '20px auto',
              animation: 'spin 1.5s linear infinite',
              boxShadow: '0 0 20px #00f7ff'
            }
          })
        );
      }
      
      if (cardList.length === 0) {
        return React.createElement('div', { className: 'empty-dashboard' },
          React.createElement('h3', null, 'No Cards Available'),
          React.createElement('p', null, 'Add card PHP files to your /cards/ directory to see them appear here.'),
          React.createElement('button', { onClick: () => window.location.reload() }, 'Refresh Dashboard')
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
            onClose: handleCardClose,
            position: cardPositions[card] || { x: 50, y: 50 },
            onPositionChange: handlePositionChange
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