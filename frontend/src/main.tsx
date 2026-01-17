import { StrictMode } from 'react'
import { createRoot } from 'react-dom/client'
import WebRoutes from './Routes/Web.tsx'

createRoot(document.getElementById('root')!).render(
  <StrictMode>
    <WebRoutes />
  </StrictMode>,
)
