import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import './Design/index.css'
import App from './Routes/App.tsx'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <App />
  </StrictMode>,
)
