<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>React + PHP Hybrid Dashboard</title>
  <link rel="stylesheet" href="public/css/styles.css">
  <script src="https://unpkg.com/react@18/umd/react.development.js"></script>
  <script src="https://unpkg.com/react-dom@18/umd/react-dom.development.js"></script>
  <script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>
  <script src="https://unpkg.com/feather-icons"></script>
</head>
<body>
  <header class="dashboard-header">
    <h1>React + PHP Hybrid Dashboard</h1>
    <p>Draggable cards with dynamic PHP content</p>
  </header>

  <main id="dashboard-root"></main>

  <script type="text/babel">
    const { useState, useRef, useEffect } = React;

    const fetchCards = async () => {
      const res = await fetch('cards.php');
      return await res.json();
    };

    const Card = ({ cardName, onClose, position, onDrag, isVisible }) => {
      const cardRef = useRef(null);
      const [isDragging, setDragging] = useState(false);
      const [offset, setOffset] = useState({ x: 0, y: 0 });

      const handleMouseDown = (e) => {
        setDragging(true);
        const rect = cardRef.current.getBoundingClientRect();
        setOffset({ x: e.clientX - rect.left, y: e.clientY - rect.top });
      };

      const handleMouseMove = (e) => {
        if (!isDragging) return;
        const newX = e.clientX - offset.x;
        const newY = e.clientY - offset.y;
        onDrag(cardName, { x: newX, y: newY });
      };

      const handleMouseUp = () => setDragging(false);

      useEffect(() => {
        if (isDragging) {
          document.addEventListener('mousemove', handleMouseMove);
          document.addEventListener('mouseup', handleMouseUp);
        }
        return () => {
          document.removeEventListener('mousemove', handleMouseMove);
          document.removeEventListener('mouseup', handleMouseUp);
        };
      }, [isDragging, offset]);

      if (!isVisible) return null;

      return (
        <div
          ref={cardRef}
          className={`card-wrapper ${isDragging ? 'dragging' : ''}`}
          style={{ top: position.y, left: position.x }}
        >
          <div className="card">
            <div className="card-header" onMouseDown={handleMouseDown}>
              <div className="card-title">
                <i data-feather="grid"></i> {cardName}
              </div>
              <button className="card-close" onClick={() => onClose(cardName)}>
                <i data-feather="x"></i>
              </button>
            </div>
            <div className="card-content">
              <iframe src={`cards/${cardName}.php`} loading="lazy"></iframe>
            </div>
          </div>
        </div>
      );
    };

    const Dashboard = () => {
      const [cards, setCards] = useState([]);
      const [positions, setPositions] = useState({});
      const [visibility, setVisibility] = useState({});
      const center = { x: window.innerWidth / 4, y: window.innerHeight / 4 };

      useEffect(() => {
        fetchCards().then(cardList => {
          const pos = {}, vis = {};
          cardList.forEach((name, i) => {
            pos[name] = { x: center.x + (i * 60), y: center.y + (i * 40) };
            vis[name] = true;
          });
          setCards(cardList);
          setPositions(pos);
          setVisibility(vis);
        });
      }, []);

      useEffect(() => {
        feather.replace();
      });

      const handleClose = (cardName) => {
        setVisibility(prev => ({ ...prev, [cardName]: false }));
      };

      const handleDrag = (cardName, pos) => {
        setPositions(prev => ({ ...prev, [cardName]: pos }));
      };

      const toggleCard = (cardName) => {
        setVisibility(prev => ({ ...prev, [cardName]: !prev[cardName] }));
      };

      const showAll = () => {
        const allVisible = Object.fromEntries(cards.map(name => [name, true]));
        setVisibility(allVisible);
      };

      const hideAll = () => {
        const allHidden = Object.fromEntries(cards.map(name => [name, false]));
        setVisibility(allHidden);
      };

      const centerAll = () => {
        const centered = {};
        cards.forEach((name, i) => {
          centered[name] = { x: center.x + (i * 50), y: center.y + (i * 50) };
        });
        setPositions(centered);
      };

      return (
        <div className="dashboard-container">
          <div className="card-wrapper settings-card" style={{ top: 100, left: 50 }}>
            <div className="card">
              <div className="card-header">
                <div className="card-title">
                  <i data-feather="settings"></i> Dashboard Settings
                </div>
              </div>
              <div className="settings-content">
                <div className="settings-section">
                  <h3>Card Visibility</h3>
                  <div className="card-toggles">
                    {cards.map(name => (
                      <label key={name} className="toggle-item">
                        <input
                          type="checkbox"
                          checked={visibility[name]}
                          onChange={() => toggleCard(name)}
                        />
                        <span className="toggle-label">{name}</span>
                      </label>
                    ))}
                  </div>
                </div>
                <div className="settings-section">
                  <h3>Global Controls</h3>
                  <div className="control-buttons">
                    <button className="control-btn" onClick={showAll}>
                      <i data-feather="eye"></i> Show All
                    </button>
                    <button className="control-btn" onClick={hideAll}>
                      <i data-feather="eye-off"></i> Hide All
                    </button>
                    <button className="control-btn" onClick={centerAll}>
                      <i data-feather="target"></i> Center All
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>

          {cards.map(name => (
            <Card
              key={name}
              cardName={name}
              isVisible={visibility[name]}
              onClose={handleClose}
              onDrag={handleDrag}
              position={positions[name] || center}
            />
          ))}
        </div>
      );
    };

    ReactDOM.render(<Dashboard />, document.getElementById('dashboard-root'));
  </script>
</body>
</html>
