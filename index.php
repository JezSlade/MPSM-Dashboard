<!DOCTYPE html>
<html lang="en" data-theme="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard</title>
  <!-- Load React -->
  <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <!-- Your existing styles -->
  <link rel="stylesheet" href="/public/css/styles.css">
  <style>
    /* Add React-specific adjustments */
    #react-root {
      position: relative;
      width: 100vw;
      height: 100vh;
    }
    .react-card {
      position: absolute;
      transition: left 0.2s ease, top 0.2s ease;
    }
  </style>
</head>
<body>
  <div id="react-root"></div>

  <script type="text/babel">
    const { useState, useRef, useEffect } = React;

    // PHP-generated card config
    const initialCards = [
      <?php 
      $cardFiles = glob(__DIR__ . '/cards/Card*.php');
      foreach($cardFiles as $i => $file) {
        $cardId = basename($file, '.php');
        if($cardId === 'CardTemplate' || $cardId === 'CardAppLog') continue;
        echo "{
          id: '".$cardId."',
          title: '".preg_replace('/(?<!^)([A-Z])/', ' $1', $cardId)."',
          x: ".($i * 300 % 900 + 50).",
          y: ".(floor($i / 3) * 250 + 50).",
          isVisible: true
        }".($i < count($cardFiles)-1 ? ',' : '');
      }
      ?>
    ];

    const CardHeader = ({ title, cardId, onToggle }) => {
      return (
        <header className="card-header flex items-center justify-between p-2 rounded-t-lg bg-gradient-to-r from-gray-700 via-gray-800 to-gray-900 shadow-md">
          <h2 className="text-base font-semibold tracking-wide truncate" title={title}>
            {title}
          </h2>
          <div className="flex items-center gap-1">
            <button className="neu-btn" data-action="minimize" data-card={cardId} aria-label="Minimize">
              <i data-feather="chevron-down"></i>
            </button>
            <button className="neu-btn" data-action="settings" data-card={cardId} aria-label="Settings">
              <i data-feather="settings"></i>
            </button>
            <button className="neu-btn" onClick={() => onToggle(cardId, false)} aria-label="Close">
              <i data-feather="x"></i>
            </button>
          </div>
        </header>
      );
    };

    const Card = ({ card, onDrag, onToggle }) => {
      const cardRef = useRef(null);
      const [isDragging, setIsDragging] = useState(false);
      const [offset, setOffset] = useState({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        if (!e.target.closest('.card-header') || e.target.closest('.neu-btn')) return;
        
        const rect = cardRef.current.getBoundingClientRect();
        setOffset({
          x: e.clientX - rect.left,
          y: e.clientY - rect.top
        });
        setIsDragging(true);
      };

      useEffect(() => {
        if (!isDragging) return;
        
        const handleMouseMove = (e) => {
          const newX = e.clientX - offset.x;
          const newY = e.clientY - offset.y;
          onDrag(card.id, newX, newY);
        };

        const handleMouseUp = () => setIsDragging(false);

        document.addEventListener('mousemove', handleMouseMove);
        document.addEventListener('mouseup', handleMouseUp);
        
        return () => {
          document.removeEventListener('mousemove', handleMouseMove);
          document.removeEventListener('mouseup', handleMouseUp);
        };
      }, [isDragging, offset]);

      if (!card.isVisible) return null;

      return (
        <div 
          ref={cardRef}
          className={`card-wrapper react-card ${isDragging ? 'dragging' : ''}`}
          style={{
            left: `${card.x}px`,
            top: `${card.y}px`,
            zIndex: isDragging ? 1000 : 1
          }}
          onMouseDown={handleMouseDown}
        >
          <CardHeader 
            title={card.title} 
            cardId={card.id}
            onToggle={onToggle}
          />
          <div className="neumorphic p-4">
            {/* Dynamic content would go here */}
            {card.id === 'ConsoleLogCard' ? (
              <div id="inCardLogContent" className="h-64 overflow-auto bg-gray-100 dark:bg-gray-800 p-2 text-xs font-mono">
                Console initialized for this card.
              </div>
            ) : (
              <p>Card content for {card.title}</p>
            )}
          </div>
        </div>
      );
    };

    const Dashboard = () => {
      const [cards, setCards] = useState(initialCards);
      const [showSettings, setShowSettings] = useState(false);

      const handleDrag = (id, newX, newY) => {
        // Constrain to viewport
        newX = Math.max(0, Math.min(newX, window.innerWidth - 280));
        newY = Math.max(0, Math.min(newY, window.innerHeight - 200));
        
        setCards(cards.map(c => c.id === id ? { ...c, x: newX, y: newY } : c));
      };

      const handleToggle = (id, isVisible) => {
        setCards(cards.map(c => c.id === id ? { ...c, isVisible } : c));
      };

      const centerCards = () => {
        const visibleCards = cards.filter(c => c.isVisible);
        if (visibleCards.length === 0) return;

        const cardWidth = 280;
        const cardHeight = 300;
        const gap = 20;
        const cardsPerRow = Math.max(1, Math.floor(window.innerWidth / (cardWidth + gap)));
        const gridWidth = Math.min(cardsPerRow, visibleCards.length) * (cardWidth + gap);
        
        setCards(cards.map((card) => {
          if (!card.isVisible) return card;
          
          const visualIndex = visibleCards.findIndex(c => c.id === card.id);
          const row = Math.floor(visualIndex / cardsPerRow);
          const col = visualIndex % cardsPerRow;
          
          return {
            ...card,
            x: (window.innerWidth - gridWidth) / 2 + col * (cardWidth + gap),
            y: 50 + row * (cardHeight + gap)
          };
        }));
      };

      return (
        <>
          <div className="settings-menu" style={{ display: showSettings ? 'block' : 'none' }}>
            <div className="neumorphic p-4 rounded-lg shadow-xl">
              <h2 className="text-lg font-semibold mb-3">Card Settings</h2>
              <div className="space-y-2">
                {cards.map(card => (
                  <label key={card.id} className="flex items-center space-x-2 text-sm">
                    <input 
                      type="checkbox" 
                      checked={card.isVisible}
                      onChange={(e) => handleToggle(card.id, e.target.checked)}
                    />
                    <span>{card.title}</span>
                  </label>
                ))}
              </div>
              <div className="mt-4 border-t pt-4 border-gray-600">
                <button className="neu-btn w-full mb-2" onClick={() => cards.forEach(c => handleToggle(c.id, true))}>
                  Show All
                </button>
                <button className="neu-btn w-full mb-2" onClick={() => cards.forEach(c => handleToggle(c.id, false))}>
                  Hide All
                </button>
                <button className="neu-btn w-full" onClick={centerCards}>
                  Center Cards
                </button>
              </div>
            </div>
          </div>

          {cards.map(card => (
            <Card 
              key={card.id}
              card={card}
              onDrag={handleDrag}
              onToggle={handleToggle}
            />
          ))}

          <button 
            className="neu-btn fixed top-4 right-4 z-50"
            onClick={() => setShowSettings(!showSettings)}
          >
            <i data-feather="settings"></i>
          </button>
        </>
      );
    };

    // Initialize the app
    const root = ReactDOM.createRoot(document.getElementById('react-root'));
    root.render(<Dashboard />);

    // Initialize Feather Icons after render
    document.addEventListener('DOMContentLoaded', () => {
      if (typeof feather !== 'undefined') {
        feather.replace();
      }
    });
  </script>
</body>
</html>