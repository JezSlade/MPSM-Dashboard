<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>React + PHP Hybrid Dashboard</title>
  <link rel="stylesheet" href="public/css/styles.css">
  <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>

  <script>
    const availableCards = <?php
      $cards = array_filter(scandir(__DIR__ . '/cards'), function ($file) {
          return preg_match('/^Card.*\\.php$/', $file);
      });
      $cardNames = array_map(function ($f) {
          return pathinfo($f, PATHINFO_FILENAME);
      }, $cards);
      echo json_encode(array_values($cardNames));
    ?>;
  </script>
</head>
<body>
  <div id="dashboard-root"></div>

  <script type="text/babel">
    const { useState, useEffect, useRef } = React;

    function Card({ cardName, onClose, position, onDrag, isVisible }) {
      const cardRef = useRef(null);
      const [isDragging, setIsDragging] = useState(false);
      const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        if (e.target.closest('.card-header')) {
          setIsDragging(true);
          const rect = cardRef.current.getBoundingClientRect();
          setDragOffset({
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
          });
        }
      };

      const handleMouseMove = (e) => {
        if (isDragging) {
          const newPosition = {
            x: e.clientX - dragOffset.x,
            y: e.clientY - dragOffset.y
          };
          onDrag(cardName, newPosition);
        }
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
      }, [isDragging, dragOffset]);

      if (!isVisible) return null;

      return (
        <div
          ref={cardRef}
          className="card"
          style={{
            position: 'absolute',
            left: position.x,
            top: position.y,
            cursor: isDragging ? 'grabbing' : 'grab'
          }}
          onMouseDown={handleMouseDown}
        >
          <div className="card-header">
            <div className="card-title">
              <i data-feather="grid"></i>
              <span>{cardName}</span>
            </div>
            <button className="card-close" onClick={() => onClose(cardName)}>
              <i data-feather="x"></i>
            </button>
          </div>
          <div className="card-content">
            <iframe
              src={`/cards/${cardName}.php`}
              frameBorder="0"
              width="100%"
              height="300"
              title={cardName}
            ></iframe>
          </div>
        </div>
      );
    }

    function SettingsCard({ cards, onToggleCard, onShowAll, onHideAll, onCenterAll, position, onDrag, isVisible }) {
      const cardRef = useRef(null);
      const [isDragging, setIsDragging] = useState(false);
      const [dragOffset, setDragOffset] = useState({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        if (e.target.closest('.card-header')) {
          setIsDragging(true);
          const rect = cardRef.current.getBoundingClientRect();
          setDragOffset({
            x: e.clientX - rect.left,
            y: e.clientY - rect.top
          });
        }
      };

      const handleMouseMove = (e) => {
        if (isDragging) {
          const newPosition = {
            x: e.clientX - dragOffset.x,
            y: e.clientY - dragOffset.y
          };
          onDrag('Settings', newPosition);
        }
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
      }, [isDragging, dragOffset]);

      if (!isVisible) return null;

      return (
        <div
          ref={cardRef}
          className="card settings-card"
          style={{
            position: 'absolute',
            left: position.x,
            top: position.y,
            cursor: isDragging ? 'grabbing' : 'grab'
          }}
          onMouseDown={handleMouseDown}
        >
          <div className="card-header">
            <div className="card-title">
              <i data-feather="settings"></i>
              <span>Dashboard Settings</span>
            </div>
          </div>
          <div className="card-content">
            <div className="settings-content">
              <div className="settings-section">
                <h3>Card Visibility</h3>
                <div className="card-toggles">
                  {availableCards.map(cardName => (
                    <label key={cardName} className="toggle-item">
                      <input
                        type="checkbox"
                        checked={cards[cardName]?.isVisible || false}
                        onChange={() => onToggleCard(cardName)}
                      />
                      <span className="toggle-label">{cardName}</span>
                    </label>
                  ))}
                </div>
              </div>
              <div className="settings-section">
                <h3>Global Controls</h3>
                <div className="control-buttons">
                  <button className="control-btn" onClick={onShowAll}>
                    <i data-feather="eye"></i> Show All
                  </button>
                  <button className="control-btn" onClick={onHideAll}>
                    <i data-feather="eye-off"></i> Hide All
                  </button>
                  <button className="control-btn" onClick={onCenterAll}>
                    <i data-feather="target"></i> Center All
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>
      );
    }

    function Dashboard() {
      const [cards, setCards] = useState(() => {
        const saved = localStorage.getItem('dashboardCards');
        if (saved) return JSON.parse(saved);

        const initial = { Settings: { isVisible: true, position: { x: 20, y: 20 } } };
        availableCards.forEach((name, i) => {
          initial[name] = {
            isVisible: i < 3,
            position: { x: 50 + (i % 3) * 320, y: 100 + Math.floor(i / 3) * 380 }
          };
        });
        return initial;
      });

      useEffect(() => {
        localStorage.setItem('dashboardCards', JSON.stringify(cards));
      }, [cards]);

      useEffect(() => {
        feather.replace();
      });

      const toggleCard = name => {
        setCards(p => ({ ...p, [name]: { ...p[name], isVisible: !p[name]?.isVisible } }));
      };

      const closeCard = name => {
        setCards(p => ({ ...p, [name]: { ...p[name], isVisible: false } }));
      };

      const dragCard = (name, pos) => {
        setCards(p => ({ ...p, [name]: { ...p[name], position: pos } }));
      };

      const showAll = () => {
        setCards(p => {
          const c = { ...p };
          [...availableCards, 'Settings'].forEach(n => c[n].isVisible = true);
          return c;
        });
      };

      const hideAll = () => {
        setCards(p => {
          const c = { ...p };
          availableCards.forEach(n => c[n].isVisible = false);
          return c;
        });
      };

      const centerAll = () => {
        setCards(p => {
          const c = { ...p };
          [...availableCards, 'Settings'].forEach((n, i) => {
            c[n].position = { x: 50 + (i % 3) * 320, y: 100 + Math.floor(i / 3) * 380 };
          });
          return c;
        });
      };

      return (
        <div className="dashboard">
          <div className="dashboard-header">
            <h1>React + PHP Hybrid Dashboard</h1>
            <p>Draggable cards with dynamic PHP content</p>
          </div>

          <div className="dashboard-content">
            <SettingsCard
              cards={cards}
              onToggleCard={toggleCard}
              onShowAll={showAll}
              onHideAll={hideAll}
              onCenterAll={centerAll}
              position={cards.Settings?.position}
              onDrag={dragCard}
              isVisible={cards.Settings?.isVisible}
            />

            {availableCards.map(name => (
              <Card
                key={name}
                cardName={name}
                onClose={closeCard}
                position={cards[name]?.position || { x: 100, y: 100 }}
                onDrag={dragCard}
                isVisible={cards[name]?.isVisible}
              />
            ))}
          </div>
        </div>
      );
    }

    ReactDOM.render(<Dashboard />, document.getElementById('dashboard-root'));
  </script>
</body>
</html>
