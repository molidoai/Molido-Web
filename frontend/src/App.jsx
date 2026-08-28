import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom'
import { AuthProvider, useAuth } from './context/AuthContext'
import AppLayout from './layouts/AppLayout'
import Login from './pages/Login'
import ForgotPassword from './pages/ForgotPassword'
import ResetPassword from './pages/ResetPassword'
import Settings from './pages/Settings'
import Workforce from './pages/Workforce'
import Register from './pages/Register'
import Dashboard from './pages/Dashboard'
import Customers from './pages/Customers'
import Leads from './pages/Leads'
import Products from './pages/Products'
import Deals from './pages/Deals'
import Orders from './pages/Orders'
import Chat from './pages/Chat'
import Modules from './pages/Modules'
import Tasks from './pages/Tasks'
import Approvals from './pages/Approvals'
import Payments from './pages/Payments'
import Subscriptions from './pages/Subscriptions'
import FeatureFlags from './pages/FeatureFlags'
import Audit from './pages/Audit'

function PrivateRoute({ children }) {
  const { user, loading } = useAuth()
  if (loading) {
    return (
      <div className="min-h-screen flex items-center justify-center text-slate-400">
        در حال بارگذاری...
      </div>
    )
  }
  return user ? children : <Navigate to="/login" replace />
}

function PublicOnly({ children }) {
  const { user, loading } = useAuth()
  if (loading) return null
  return user ? <Navigate to="/" replace /> : children
}

export default function App() {
  return (
    <AuthProvider>
      <BrowserRouter>
        <Routes>
          <Route
            path="/login"
            element={
              <PublicOnly>
                <Login />
              </PublicOnly>
            }
          />
          <Route
            path="/register"
            element={
              <PublicOnly>
                <Register />
              </PublicOnly>
            }
          />
          <Route
            path="/"
            element={
              <PrivateRoute>
                <AppLayout />
              </PrivateRoute>
            }
          >
            <Route index element={<Dashboard />} />
            <Route path="customers" element={<Customers />} />
            <Route path="leads" element={<Leads />} />
            <Route path="products" element={<Products />} />
            <Route path="deals" element={<Deals />} />
            <Route path="orders" element={<Orders />} />
            <Route path="chat" element={<Chat />} />
            <Route path="modules" element={<Modules />} />
            <Route path="tasks" element={<Tasks />} />
            <Route path="approvals" element={<Approvals />} />
            <Route path="payments" element={<Payments />} />
            <Route path="subscriptions" element={<Subscriptions />} />
            <Route path="feature-flags" element={<FeatureFlags />} />
            <Route path="settings" element={<Settings />} />
            <Route path="workforce" element={<Workforce />} />
            <Route path="audit" element={<Audit />} />
          </Route>
          <Route path="*" element={<Navigate to="/" replace />} />
        </Routes>
      </BrowserRouter>
    </AuthProvider>
  )
}
