<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>React Dashboard</title>
  <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <style>
    body {
      margin: 0;
      font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
      background: #1f2937;
      color: white;
      overflow: hidden;
    }
    
    .dashboard {
      position: relative;
      width: 100vw;
      height: 100vh;
    }
    
    .card {
      position: absolute;
      width: 280px;
      background: #374151;
      border-radius: 8px;
      box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
      cursor: grab;
      user-select: none;
      transition: transform 0.1s ease, box-shadow 0.1s ease;
    }
    
    .card.dragging {
      cursor: grabbing;
      transform: scale(1.02);
      box-shadow: 0 10px 15px rgba(0, 0, 0, 0.2);
      z-index: 100;
    }
    
    .card-header {
      padding: 12px;
      background: #4b5563;
      border-top-left-radius: 8px;
      border-top-right-radius: 8px;
      display: flex;
      justify-content: space-between;
      align-items: center;
    }
    
    .card-content {
      padding: 16px;
    }
    
    .settings-panel {
      position: absolute;
      top: 20px;
      left: 20px;
      z-index: 1000;
      background: #2D3748;
      padding: 16px;
      border-radius: 8px;
    }
  </style>
</head>
<body>
  <div id="root"></div>

  <script type="text/babel">
    const { useState, useRef, useEffect } = React;

    const Card = ({ id, title, x, y, isVisible, onDrag, onToggle }) => {
      const cardRef = useRef(null);
      const [isDragging, setIsDragging] = useState(false);
      const [offset, setOffset] = useState({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        if (e.button !== 0) return; // Only left mouse button
        
        const rect = cardRef.current.getBoundingClientRect();
        setOffset({
          x: e.clientX - rect.left,
          y: e.clientY - rect.top
        });
        setIsDragging(true);
        e.stopPropagation();
      };

      const handleMouseMove = (e) => {
        if (!isDragging) return;
        
        const newX = e.clientX - offset.x;
        const newY = e.clientY - offset.y;
        onDrag(id, newX, newY);
      };

      const handleMouseUp = () => {
        setIsDragging(false);
      };

      useEffect(() => {
        if (isDragging) {
          document.addEventListener('mousemove', handleMouseMove);
          document.addEventListener('mouseup', handleMouseUp);
          return () => {
            document.removeEventListener('mousemove', handleMouseMove);
            document.removeEventListener('mouseup', handleMouseUp);
          };
        }
      }, [isDragging, offset]);

      if (!isVisible) return null;

      return (
        <div
          ref={cardRef}
          className={`card ${isDragging ? 'dragging' : ''}`}
          style={{
            left: `${x}px`,
            top: `${y}px`,
            display: isVisible ? 'block' : 'none'
          }}
          onMouseDown={handleMouseDown}
        >
          <div className="card-header">
            <h3>{title}</h3>
            <button onClick={() => onToggle(id, false)}>×</button>
          </div>
          <div className="card-content">
            {id === 'ConsoleLogCard' ? (
              <div style={{ height: '200px', overflow: 'auto', background: '#1f2937' }}>
                Console log content would go here...
              </div>
            ) : (
              <p>Card content for {title}</p>
            )}
          </div>
        </div>
      );
    };

    const Dashboard = () => {
      const [cards, setCards] = useState([
        { id: 'ConsoleLogCard', title: 'Console Log', x: 50, y: 50, isVisible: true },
        { id: 'Card1', title: 'Performance', x: 350, y: 50, isVisible: true },
        { id: 'Card2', title: 'Statistics', x: 650, y: 50, isVisible: true },
        { id: 'Card3', title: 'Settings', x: 50, y: 300, isVisible: true },
        { id: 'Card4', title: 'Alerts', x: 350, y: 300, isVisible: true },
        { id: 'Card5', title: 'Metrics', x: 650, y: 300, isVisible: true }
      ]);

      const handleDrag = (id, newX, newY) => {
        // Constrain to viewport
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const cardWidth = 280; // Match your CSS
        
        newX = Math.max(0, Math.min(newX, viewportWidth - cardWidth));
        newY = Math.max(0, Math.min(newY, viewportHeight - 200)); // Approx card height
        
        setCards(cards.map(card => 
          card.id === id ? { ...card, x: newX, y: newY } : card
        ));
      };

      const handleToggle = (id, isVisible) => {
        setCards(cards.map(card => 
          card.id === id ? { ...card, isVisible } : card
        ));
      };

      const centerCards = () => {
        const visibleCards = cards.filter(card => card.isVisible);
        if (visibleCards.length === 0) return;
        
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const cardWidth = 280;
        const cardHeight = 200; // Approximate
        const gap = 20;
        
        const cardsPerRow = Math.max(1, Math.floor(viewportWidth / (cardWidth + gap)));
        const gridWidth = Math.min(cardsPerRow, visibleCards.length) * (cardWidth + gap) - gap;
        const startX = (viewportWidth - gridWidth) / 2;
        const startY = 50; // Top padding
        
        setCards(cards.map((card, index) => {
          if (!card.isVisible) return card;
          
          const row = Math.floor(index / cardsPerRow);
          const col = index % cardsPerRow;
          
          return {
            ...card,
            x: startX + col * (cardWidth + gap),
            y: startY + row * (cardHeight + gap)
          };
        }));
      };

      return (
        <div className="dashboard">
          <div className="settings-panel">
            <h3>Card Controls</h3>
            {cards.map(card => (
              <div key={card.id}>
                <label>
                  <input
                    type="checkbox"
                    checked={card.isVisible}
                    onChange={(e) => handleToggle(card.id, e.target.checked)}
                  />
                  {card.title}
                </label>
              </div>
            ))}
            <button onClick={centerCards}>Center Cards</button>
          </div>
          
          {cards.map(card => (
            <Card
              key={card.id}
              {...card}
              onDrag={handleDrag}
              onToggle={handleToggle}
            />
          ))}
        </div>
      );
    };

    const root = ReactDOM.createRoot(document.getElementById('root'));
    root.render(<Dashboard />);
  </script>
</body>
</html>