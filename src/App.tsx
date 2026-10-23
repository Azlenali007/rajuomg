import React, { useState, useEffect } from 'react';
import {
  Shield, Phone, MessageSquare, Key, Wallet, CreditCard, QrCode,
  RefreshCw, CheckCircle2, Clock, AlertTriangle, XCircle, Copy, Check,
  Search, Filter, ChevronRight, Sliders, FileText, Database, Terminal,
  Users, Layers, ExternalLink, Send, ArrowRight, ArrowLeft, Eye
} from 'lucide-react';

interface ServiceItem {
  id: number;
  code: string;
  name: string;
  category: string;
  iconName: string;
  price: number;
  providerCost: number;
  stock: number;
  stockStatus: 'AVAILABLE' | 'UNAVAILABLE' | 'LOW';
  isActive: boolean;
}

interface OrderItem {
  id: number;
  orderNumber: string;
  serviceId: number;
  serviceName: string;
  countryName: string;
  phoneNumber: string;
  activationId: string;
  price: number;
  status: 'WAITING' | 'RECEIVED' | 'CANCELLED' | 'EXPIRED';
  otpCode: string | null;
  createdAt: string;
  expiresInSeconds: number;
}

interface LedgerTx {
  id: number;
  type: 'credit' | 'debit' | 'refund' | 'admin_adjustment';
  amount: number;
  balanceAfter: number;
  reference: string;
  description: string;
  date: string;
}

interface ManualPaymentReq {
  id: number;
  userName: string;
  userEmail: string;
  amount: number;
  reference: string;
  status: 'PENDING' | 'APPROVED' | 'REJECTED';
  date: string;
}

const INITIAL_SERVICES: ServiceItem[] = [
  { id: 1, code: 'wa', name: 'WhatsApp', category: 'messaging', iconName: 'MessageSquare', price: 0.85, providerCost: 0.50, stock: 840, stockStatus: 'AVAILABLE', isActive: true },
  { id: 2, code: 'tg', name: 'Telegram', category: 'messaging', iconName: 'Send', price: 0.65, providerCost: 0.35, stock: 1200, stockStatus: 'AVAILABLE', isActive: true },
  { id: 3, code: 'go', name: 'Google / Gmail / YouTube', category: 'technology', iconName: 'Mail', price: 0.90, providerCost: 0.60, stock: 450, stockStatus: 'AVAILABLE', isActive: true },
  { id: 4, code: 'ig', name: 'Instagram', category: 'social', iconName: 'Camera', price: 0.70, providerCost: 0.45, stock: 920, stockStatus: 'AVAILABLE', isActive: true },
  { id: 5, code: 'ds', name: 'Discord', category: 'gaming', iconName: 'Gamepad', price: 0.55, providerCost: 0.30, stock: 610, stockStatus: 'AVAILABLE', isActive: true },
  { id: 6, code: 'oa', name: 'OpenAI / ChatGPT', category: 'ai', iconName: 'Cpu', price: 1.20, providerCost: 0.75, stock: 350, stockStatus: 'AVAILABLE', isActive: true },
  { id: 7, code: 'tw', name: 'Twitter / X', category: 'social', iconName: 'Share2', price: 0.75, providerCost: 0.48, stock: 510, stockStatus: 'AVAILABLE', isActive: true },
  { id: 8, code: 'ms', name: 'Microsoft / Outlook', category: 'technology', iconName: 'Briefcase', price: 0.80, providerCost: 0.52, stock: 380, stockStatus: 'AVAILABLE', isActive: true },
  { id: 9, code: 'tt', name: 'TikTok', category: 'video', iconName: 'Video', price: 0.60, providerCost: 0.38, stock: 780, stockStatus: 'AVAILABLE', isActive: true },
  { id: 10, code: 'am', name: 'Amazon', category: 'shopping', iconName: 'ShoppingBag', price: 0.95, providerCost: 0.65, stock: 290, stockStatus: 'AVAILABLE', isActive: true },
  { id: 11, code: 'ub', name: 'Uber', category: 'transport', iconName: 'Navigation', price: 0.70, providerCost: 0.45, stock: 430, stockStatus: 'AVAILABLE', isActive: true },
  { id: 12, code: 'st', name: 'Steam', category: 'gaming', iconName: 'Flame', price: 0.50, providerCost: 0.30, stock: 950, stockStatus: 'AVAILABLE', isActive: true },
];

const INITIAL_COUNTRIES = [
  { id: 1, code: 'in', name: 'India', dial: '+91' },
  { id: 2, code: 'us', name: 'United States', dial: '+1' },
  { id: 3, code: 'gb', name: 'United Kingdom', dial: '+44' },
  { id: 4, code: 'id', name: 'Indonesia', dial: '+62' },
  { id: 5, code: 'br', name: 'Brazil', dial: '+55' },
  { id: 6, code: 'ph', name: 'Philippines', dial: '+63' },
];

export default function App() {
  // Navigation mode
  const [activePortal, setActivePortal] = useState<'user' | 'admin' | 'installer' | 'code'>('user');
  const [userTab, setUserTab] = useState<'services' | 'order' | 'orders' | 'wallet'>('services');
  const [adminTab, setAdminTab] = useState<'dashboard' | 'providers' | 'services' | 'orders' | 'users' | 'payments'>('dashboard');

  // Web Installer State (8 Steps)
  const [installerStep, setInstallerStep] = useState<number>(1);
  const [isSystemInstalled, setIsSystemInstalled] = useState<boolean>(true); // Can be toggled for demo
  const [installDbHost, setInstallDbHost] = useState<string>('127.0.0.1');
  const [installDbPort, setInstallDbPort] = useState<number>(3306);
  const [installDbName, setInstallDbName] = useState<string>('otp_platform');
  const [installDbUser, setInstallDbUser] = useState<string>('root');
  const [installDbPass, setInstallDbPass] = useState<string>('');
  const [installDbTested, setInstallDbTested] = useState<boolean>(false);
  const [installDbTesting, setInstallDbTesting] = useState<boolean>(false);
  const [installDbMsg, setInstallDbMsg] = useState<string | null>(null);

  const [installSiteName, setInstallSiteName] = useState<string>('OTP Doctor Verification');
  const [installAppUrl, setInstallAppUrl] = useState<string>(window.location.origin);
  const [installTimezone, setInstallTimezone] = useState<string>('UTC');
  const [installCurrency, setInstallCurrency] = useState<string>('USD');

  const [installAdminName, setInstallAdminName] = useState<string>('Super Administrator');
  const [installAdminEmail, setInstallAdminEmail] = useState<string>('admin@otpdoctor.in');
  const [installAdminPass, setInstallAdminPass] = useState<string>('AdminSecure#2026');
  const [installingProgress, setInstallingProgress] = useState<boolean>(false);
  const [installLogs, setInstallLogs] = useState<string[]>([]);

  // User state
  const [walletBalance, setWalletBalance] = useState<number>(14.50);
  const [services, setServices] = useState<ServiceItem[]>(INITIAL_SERVICES);
  const [selectedCountry, setSelectedCountry] = useState<number>(1);
  const [searchQuery, setSearchQuery] = useState<string>('');
  
  // Orders state
  const [orders, setOrders] = useState<OrderItem[]>([
    {
      id: 101,
      orderNumber: 'ORD-74B9A2',
      serviceId: 1,
      serviceName: 'WhatsApp',
      countryName: 'India',
      phoneNumber: '919876543210',
      activationId: '94827104',
      price: 0.85,
      status: 'RECEIVED',
      otpCode: '849-204',
      createdAt: '10 mins ago',
      expiresInSeconds: 0
    }
  ]);
  const [activeOrderId, setActiveOrderId] = useState<number | null>(null);

  // Ledger state
  const [transactions, setTransactions] = useState<LedgerTx[]>([
    { id: 1, type: 'credit', amount: 15.00, balanceAfter: 15.00, reference: 'PAY_RZP_INIT', description: 'Razorpay recharge (Simulated)', date: 'Today, 09:30' },
    { id: 2, type: 'debit', amount: 0.85, balanceAfter: 14.15, reference: 'ORD-74B9A2', description: 'WhatsApp verification number purchase', date: 'Today, 09:42' },
    { id: 3, type: 'refund', amount: 0.85, balanceAfter: 15.00, reference: 'ORD-74B9A2', description: 'Refund for cancelled test number', date: 'Today, 09:44' },
    { id: 4, type: 'debit', amount: 0.85, balanceAfter: 14.15, reference: 'ORD-74B9A2', description: 'WhatsApp verification number order', date: 'Today, 09:45' }
  ]);

  // Admin Manual Payments Queue
  const [manualRequests, setManualRequests] = useState<ManualPaymentReq[]>([
    { id: 1, userName: 'Alex Johnson', userEmail: 'alex@example.com', amount: 25.00, reference: 'UTR_948271849102', status: 'PENDING', date: '15 mins ago' },
    { id: 2, userName: 'Sarah Miller', userEmail: 'sarah.m@gmail.com', amount: 50.00, reference: 'TXN_0492817482', status: 'PENDING', date: '1 hour ago' }
  ]);

  // Provider config & test state
  const [providerBalance, setProviderBalance] = useState<number>(254.80);
  const [providerTesting, setProviderTesting] = useState<boolean>(false);
  const [providerTestMsg, setProviderTestMsg] = useState<string | null>(null);

  // Copy feedback
  const [copiedText, setCopiedText] = useState<string | null>(null);
  const [toastMsg, setToastMsg] = useState<string | null>(null);

  const showToast = (msg: string) => {
    setToastMsg(msg);
    setTimeout(() => setToastMsg(null), 3000);
  };

  const copyToClipboard = (text: string) => {
    navigator.clipboard.writeText(text);
    setCopiedText(text);
    showToast(`Copied to clipboard: ${text}`);
    setTimeout(() => setCopiedText(null), 2000);
  };

  // Active Order Live Monitor Timer & Auto-Polling
  const activeOrder = orders.find(o => o.id === activeOrderId);

  useEffect(() => {
    if (!activeOrderId) return;
    const interval = setInterval(() => {
      setOrders(prev => prev.map(ord => {
        if (ord.id !== activeOrderId || ord.status !== 'WAITING') return ord;
        
        // Countdown
        const nextTime = Math.max(0, ord.expiresInSeconds - 1);

        // Auto-deliver simulated OTP between 5 and 8 seconds for demonstration
        if (nextTime <= 892 && !ord.otpCode) {
          const generatedOtp = Math.floor(100000 + Math.random() * 900000).toString();
          showToast(`✔ SMS Delivered from Provider: ${generatedOtp}`);
          return {
            ...ord,
            status: 'RECEIVED',
            otpCode: generatedOtp,
            expiresInSeconds: nextTime
          };
        }

        // Auto-expire when countdown reaches 0
        if (nextTime === 0) {
          // Trigger refund
          setWalletBalance(b => b + ord.price);
          setTransactions(txs => [
            {
              id: Date.now(),
              type: 'refund',
              amount: ord.price,
              balanceAfter: walletBalance + ord.price,
              reference: ord.orderNumber,
              description: `100% Unused Number Auto-Refund #${ord.orderNumber}`,
              date: 'Just now'
            },
            ...txs
          ]);
          showToast(`Order #${ord.orderNumber} expired. 100% refunded to wallet.`);
          return {
            ...ord,
            status: 'EXPIRED',
            expiresInSeconds: 0
          };
        }

        return { ...ord, expiresInSeconds: nextTime };
      }));
    }, 1000);

    return () => clearInterval(interval);
  }, [activeOrderId, walletBalance]);

  // Order Number Action
  const handleOrderNumber = (srv: ServiceItem) => {
    if (walletBalance < srv.price) {
      showToast(`Insufficient balance! Please add at least $${(srv.price - walletBalance).toFixed(2)} to your wallet.`);
      setUserTab('wallet');
      return;
    }

    const country = INITIAL_COUNTRIES.find(c => c.id === selectedCountry) || INITIAL_COUNTRIES[0];
    const newBal = +(walletBalance - srv.price).toFixed(4);
    setWalletBalance(newBal);

    const newOrderId = Date.now();
    const orderNum = 'ORD-' + Math.random().toString(36).substring(2, 8).toUpperCase();
    const randomDigits = Math.floor(1000000000 + Math.random() * 9000000000);
    const assignedPhone = country.dial.replace('+', '') + randomDigits;
    const activationId = Math.floor(10000000 + Math.random() * 90000000).toString();

    const newOrder: OrderItem = {
      id: newOrderId,
      orderNumber: orderNum,
      serviceId: srv.id,
      serviceName: srv.name,
      countryName: country.name,
      phoneNumber: assignedPhone,
      activationId: activationId,
      price: srv.price,
      status: 'WAITING',
      otpCode: null,
      createdAt: 'Just now',
      expiresInSeconds: 900 // 15 mins
    };

    setOrders([newOrder, ...orders]);
    setTransactions([
      {
        id: Date.now(),
        type: 'debit',
        amount: srv.price,
        balanceAfter: newBal,
        reference: orderNum,
        description: `${srv.name} verification number allocation`,
        date: 'Just now'
      },
      ...transactions
    ]);

    setActiveOrderId(newOrderId);
    setUserTab('order');
    showToast(`Phone number allocated: +${assignedPhone}`);
  };

  // Cancel & Refund Order
  const handleCancelOrder = (ord: OrderItem) => {
    if (ord.status !== 'WAITING') return;

    const newBal = +(walletBalance + ord.price).toFixed(4);
    setWalletBalance(newBal);

    setOrders(prev => prev.map(o => o.id === ord.id ? { ...o, status: 'CANCELLED' } : o));
    setTransactions([
      {
        id: Date.now(),
        type: 'refund',
        amount: ord.price,
        balanceAfter: newBal,
        reference: ord.orderNumber,
        description: `Cancelled & 100% refunded #${ord.orderNumber}`,
        date: 'Just now'
      },
      ...transactions
    ]);

    showToast(`Order cancelled. $${ord.price.toFixed(2)} refunded to your wallet!`);
  };

  // Razorpay Quick Recharge
  const handleRazorpayRecharge = (amount: number) => {
    const newBal = +(walletBalance + amount).toFixed(4);
    setWalletBalance(newBal);
    setTransactions([
      {
        id: Date.now(),
        type: 'credit',
        amount: amount,
        balanceAfter: newBal,
        reference: 'RZP_' + Math.random().toString(36).substring(2, 10).toUpperCase(),
        description: `Razorpay Verified Deposit`,
        date: 'Just now'
      },
      ...transactions
    ]);
    showToast(`Payment successful! $${amount.toFixed(2)} credited to your wallet.`);
  };

  // Submit Manual QR Proof
  const [manualAmt, setManualAmt] = useState<string>('15.00');
  const [manualUtr, setManualUtr] = useState<string>('');
  const handleManualSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (!manualUtr.trim()) {
      showToast('Please enter your UTR / Reference ID.');
      return;
    }
    const req: ManualPaymentReq = {
      id: Date.now(),
      userName: 'Current User',
      userEmail: 'user@example.com',
      amount: parseFloat(manualAmt) || 10,
      reference: manualUtr.trim(),
      status: 'PENDING',
      date: 'Just now'
    };
    setManualRequests([req, ...manualRequests]);
    setManualUtr('');
    showToast('Manual payment proof submitted! Pending Super Admin approval.');
  };

  // Admin Approve Manual Payment
  const handleApproveManual = (req: ManualPaymentReq) => {
    setManualRequests(prev => prev.map(m => m.id === req.id ? { ...m, status: 'APPROVED' } : m));
    setWalletBalance(b => +(b + req.amount).toFixed(4));
    setTransactions(txs => [
      {
        id: Date.now(),
        type: 'credit',
        amount: req.amount,
        balanceAfter: +(walletBalance + req.amount).toFixed(4),
        reference: req.reference,
        description: `Manual QR Approved by Super Admin (Ref: ${req.reference})`,
        date: 'Just now'
      },
      ...txs
    ]);
    showToast(`Approved & Credited $${req.amount.toFixed(2)} for ${req.userName}`);
  };

  // Admin Reject Manual Payment
  const handleRejectManual = (req: ManualPaymentReq) => {
    setManualRequests(prev => prev.map(m => m.id === req.id ? { ...m, status: 'REJECTED' } : m));
    showToast(`Rejected payment request #${req.id}`);
  };

  // Provider Test Handshake
  const handleTestProvider = () => {
    setProviderTesting(true);
    setProviderTestMsg(null);
    setTimeout(() => {
      setProviderTesting(false);
      setProviderBalance(254.80);
      setProviderTestMsg('✔ Connection verified! OTP Doctor API responded HTTP 200 (Balance: 254.80 RUB)');
      showToast('Provider API connection verified!');
    }, 900);
  };

  // Web Installer Step Handlers
  const handleTestInstallerDb = () => {
    if (!installDbName.trim()) {
      setInstallDbMsg('Database name cannot be empty.');
      setInstallDbTested(false);
      return;
    }
    setInstallDbTesting(true);
    setInstallDbMsg(null);
    setTimeout(() => {
      setInstallDbTesting(false);
      setInstallDbTested(true);
      setInstallDbMsg(`✔ PDO MySQL Handshake Successful! Connected to database '${installDbName}' at ${installDbHost}:${installDbPort}.`);
      showToast('Database connection verified!');
    }, 800);
  };

  const handleRunInstallerExecution = () => {
    if (installAdminPass.length < 8) {
      showToast('Super Admin password must be at least 8 characters long.');
      return;
    }
    setInstallingProgress(true);
    setInstallerStep(6);
    setInstallLogs([
      'Initiating PDO connection to MySQL 8+ engine...',
      `Database selected: ${installDbName}`,
      'Reading and parsing database/schema.sql...',
      'Creating tables: users, wallets, wallet_transactions, providers, countries, services, provider_services, orders, payments, manual_payment_requests, audit_logs...',
      'Applying performance indexes and foreign key constraints from database/indexes.sql...',
      'Seeding initial verification services and OTP Doctor provider mappings from database/seed.sql...',
      `Provisioning Single Super Admin account (${installAdminEmail}) with bcrypt hash...`,
      'Configuring site settings (name, base URL, currency)...',
      'Generating production config/database.php and .env file...',
      'Creating permanent lock: storage/installed.lock...',
      'Installation execution finished successfully!'
    ]);

    setTimeout(() => {
      setInstallingProgress(false);
      setIsSystemInstalled(true);
      setInstallerStep(8);
      showToast('✔ Installation completed successfully! Application locked.');
    }, 2200);
  };

  return (
    <div className="min-h-screen bg-[#faf8ff] text-slate-800 flex flex-col font-sans">
      
      {/* Toast Notification */}
      {toastMsg && (
        <div className="fixed top-5 right-5 z-50 bg-slate-900 text-white px-4 py-2.5 rounded-xl shadow-xl text-xs font-semibold flex items-center gap-2 border border-slate-700 animate-in fade-in slide-in-from-top-2">
          <CheckCircle2 className="w-4 h-4 text-purple-400" />
          <span>{toastMsg}</span>
        </div>
      )}

      {/* Primary Top Bar (Contract: 3 Zones separated by gap-8) */}
      <header className="bg-white border-b border-purple-100 sticky top-0 z-40">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
          <div className="flex items-center justify-between h-16 gap-8">
            
            {/* Zone 1: Single text element wordmark */}
            <div className="flex items-center gap-3">
              <div className="w-9 h-9 rounded-xl bg-purple-700 text-white flex items-center justify-center font-extrabold text-sm shadow-sm">
                OD
              </div>
              <span className="text-lg font-bold tracking-tight text-slate-900 whitespace-nowrap">
                OTP Doctor
              </span>
            </div>

            {/* Zone 2: Navigation Links */}
            <nav className="hidden md:flex items-center gap-6 text-sm font-medium">
              <button 
                onClick={() => { setActivePortal('user'); setUserTab('services'); }}
                className={`transition-colors whitespace-nowrap ${activePortal === 'user' && userTab === 'services' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-slate-600 hover:text-purple-700'}`}
              >
                Services Catalog
              </button>
              <button 
                onClick={() => { setActivePortal('user'); setUserTab('orders'); }}
                className={`transition-colors whitespace-nowrap ${activePortal === 'user' && userTab === 'orders' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-slate-600 hover:text-purple-700'}`}
              >
                My Orders ({orders.length})
              </button>
              <button 
                onClick={() => { setActivePortal('user'); setUserTab('wallet'); }}
                className={`transition-colors whitespace-nowrap ${activePortal === 'user' && userTab === 'wallet' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-slate-600 hover:text-purple-700'}`}
              >
                Wallet & Ledger
              </button>
              <button 
                onClick={() => setActivePortal('admin')}
                className={`flex items-center gap-1.5 transition-colors whitespace-nowrap ${activePortal === 'admin' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-purple-600 hover:text-purple-800'}`}
              >
                <Shield className="w-4 h-4 text-purple-600" />
                <span>Super Admin Panel</span>
              </button>
              <button 
                onClick={() => setActivePortal('installer')}
                className={`flex items-center gap-1.5 transition-colors whitespace-nowrap ${activePortal === 'installer' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-slate-600 hover:text-purple-700'}`}
              >
                <Terminal className="w-4 h-4 text-purple-600" />
                <span>Web Installer (/install)</span>
                <span className={`text-[10px] px-1.5 py-0.2 rounded font-mono font-bold ${isSystemInstalled ? 'bg-slate-100 text-slate-500' : 'bg-amber-100 text-amber-800'}`}>
                  {isSystemInstalled ? 'LOCKED' : 'READY'}
                </span>
              </button>
              <button 
                onClick={() => setActivePortal('code')}
                className={`flex items-center gap-1.5 transition-colors whitespace-nowrap ${activePortal === 'code' ? 'text-purple-700 font-bold border-b-2 border-purple-700 pb-0.5' : 'text-slate-500 hover:text-slate-800'}`}
              >
                <Database className="w-4 h-4 text-slate-500" />
                <span>PHP & SQL Files</span>
              </button>
            </nav>

            {/* Zone 3: Primary Action / User Wallet Badge */}
            <div className="flex items-center gap-3 shrink-0">
              <button 
                onClick={() => { setActivePortal('user'); setUserTab('wallet'); }}
                className="bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl px-3.5 py-1.5 flex items-center gap-2.5 transition-colors shadow-2xs"
              >
                <Wallet className="w-4 h-4 text-purple-700" />
                <div className="text-left">
                  <span className="text-[10px] text-purple-600 font-medium block leading-none">Wallet</span>
                  <span className="font-mono tabular-nums font-bold text-slate-900 text-xs">${walletBalance.toFixed(2)}</span>
                </div>
              </button>
            </div>

          </div>
        </div>
      </header>

      {/* Main Container Viewport */}
      <main className="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">

        {/* =========================================================================
            USER MARKETPLACE PORTAL
            ========================================================================= */}
        {activePortal === 'user' && (
          <div className="space-y-8">

            {/* Sub-Tabs Navigation */}
            <div className="flex items-center justify-between border-b border-slate-200 pb-4">
              <div className="flex items-center gap-2 bg-slate-100 p-1 rounded-xl text-xs font-semibold">
                <button 
                  onClick={() => setUserTab('services')}
                  className={`px-3.5 py-1.5 rounded-lg transition-colors ${userTab === 'services' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Services
                </button>
                {activeOrder && (
                  <button 
                    onClick={() => setUserTab('order')}
                    className={`px-3.5 py-1.5 rounded-lg flex items-center gap-1.5 transition-colors ${userTab === 'order' ? 'bg-white text-purple-700 shadow-xs font-bold' : 'text-purple-600 hover:text-purple-800'}`}
                  >
                    <span className="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span>Live Order #{activeOrder.orderNumber}</span>
                  </button>
                )}
                <button 
                  onClick={() => setUserTab('orders')}
                  className={`px-3.5 py-1.5 rounded-lg transition-colors ${userTab === 'orders' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Orders History
                </button>
                <button 
                  onClick={() => setUserTab('wallet')}
                  className={`px-3.5 py-1.5 rounded-lg transition-colors ${userTab === 'wallet' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Add Money & Ledger
                </button>
              </div>

              <div className="hidden sm:flex items-center gap-2 text-xs text-slate-500 font-medium">
                <span>Provider: <strong className="text-slate-700 font-mono">OTP Doctor Global Node</strong></span>
                <span aria-hidden="true">·</span>
                <span>Auto-Refund: <strong className="text-emerald-700">15 Mins</strong></span>
              </div>
            </div>

            {/* TAB 1: SERVICES CATALOG */}
            {userTab === 'services' && (
              <div className="space-y-6">
                
                {/* Search & Country Filter Row */}
                <div className="grid grid-cols-1 md:grid-cols-4 gap-4">
                  <div className="md:col-span-3 relative">
                    <Search className="w-4 h-4 text-slate-400 absolute left-3.5 top-3" />
                    <input 
                      type="text" 
                      value={searchQuery}
                      onChange={(e) => setSearchQuery(e.target.value)}
                      placeholder="Search verification services (WhatsApp, Telegram, Google, Discord, OpenAI)..."
                      className="w-full pl-10 pr-4 py-2.5 bg-white border border-slate-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-purple-600 shadow-xs"
                    />
                  </div>
                  <div>
                    <select 
                      value={selectedCountry}
                      onChange={(e) => setSelectedCountry(Number(e.target.value))}
                      className="w-full px-3 py-2.5 bg-white border border-slate-200 rounded-xl text-sm font-medium text-slate-800 focus:outline-none focus:ring-2 focus:ring-purple-600 shadow-xs"
                    >
                      {INITIAL_COUNTRIES.map(c => (
                        <option key={c.id} value={c.id}>{c.name} ({c.dial})</option>
                      ))}
                    </select>
                  </div>
                </div>

                {/* Services Grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                  {services
                    .filter(s => s.name.toLowerCase().includes(searchQuery.toLowerCase()) || s.code.includes(searchQuery.toLowerCase()))
                    .map(srv => (
                      <div 
                        key={srv.id} 
                        className="bg-white border border-slate-200 rounded-2xl p-5 flex flex-col justify-between hover:border-purple-300 hover:shadow-md transition-all group"
                      >
                        <div>
                          <div className="flex items-start justify-between gap-2 mb-3">
                            <div className="w-10 h-10 rounded-xl bg-purple-50 text-purple-700 flex items-center justify-center font-bold text-sm group-hover:bg-purple-700 group-hover:text-white transition-colors">
                              {srv.code.toUpperCase()}
                            </div>
                            <span className="text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                              {srv.stock} In Stock
                            </span>
                          </div>
                          <h3 className="text-base font-bold text-slate-900">{srv.name}</h3>
                          <span className="text-xs text-slate-400 capitalize block mt-0.5">{srv.category} Service</span>
                        </div>

                        <div className="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                          <div>
                            <span className="text-[10px] text-slate-400 block font-medium">Price / SMS</span>
                            <span className="text-base font-bold font-mono tabular-nums text-slate-900">
                              ${srv.price.toFixed(2)}
                            </span>
                          </div>

                          <button 
                            onClick={() => handleOrderNumber(srv)}
                            className="px-3.5 py-2 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors flex items-center gap-1.5"
                          >
                            <span>Get Number</span>
                            <ChevronRight className="w-3.5 h-3.5" />
                          </button>
                        </div>
                      </div>
                    ))}
                </div>

              </div>
            )}

            {/* TAB 2: LIVE ORDER MONITOR */}
            {userTab === 'order' && activeOrder && (
              <div className="max-w-2xl mx-auto space-y-6">
                
                <div className="bg-white border border-purple-200 rounded-2xl p-6 sm:p-8 shadow-sm space-y-6">
                  
                  {/* Order Header */}
                  <div className="flex items-center justify-between pb-6 border-b border-slate-100">
                    <div>
                      <span className="text-xs font-bold text-purple-700 uppercase tracking-wider block">Activation #{activeOrder.orderNumber}</span>
                      <h2 className="text-xl font-bold text-slate-900 mt-0.5">{activeOrder.serviceName} ({activeOrder.countryName})</h2>
                    </div>

                    <div className="text-right">
                      <span className={`inline-block px-3 py-1 rounded-full text-xs font-bold font-mono ${
                        activeOrder.status === 'RECEIVED' ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' :
                        activeOrder.status === 'WAITING' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                        'bg-slate-100 text-slate-600'
                      }`}>
                        {activeOrder.status === 'WAITING' ? 'WAITING FOR SMS' : activeOrder.status}
                      </span>
                      {activeOrder.status === 'WAITING' && (
                        <span className="block text-[11px] text-slate-400 mt-1 font-mono tabular-nums">
                          Expires in: {Math.floor(activeOrder.expiresInSeconds / 60)}:{(activeOrder.expiresInSeconds % 60).toString().padStart(2, '0')}
                        </span>
                      )}
                    </div>
                  </div>

                  {/* Phone Number Presentation with 1-Click Copy */}
                  <div className="bg-slate-50 border border-slate-200 rounded-xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div>
                      <span className="text-xs text-slate-500 font-medium block">Allocated Verification Number</span>
                      <span className="text-2xl font-bold font-mono tabular-nums text-slate-900 tracking-wider">
                        +{activeOrder.phoneNumber}
                      </span>
                    </div>

                    <button 
                      onClick={() => copyToClipboard(activeOrder.phoneNumber)}
                      className="px-4 py-2 text-xs font-semibold text-slate-700 bg-white border border-slate-300 hover:bg-slate-100 rounded-lg shadow-xs transition-colors flex items-center gap-1.5"
                    >
                      {copiedText === activeOrder.phoneNumber ? <Check className="w-3.5 h-3.5 text-emerald-600" /> : <Copy className="w-3.5 h-3.5" />}
                      <span>Copy Number</span>
                    </button>
                  </div>

                  {/* Live OTP Box */}
                  <div className={`p-8 text-center rounded-2xl border-2 ${activeOrder.otpCode ? 'bg-emerald-50 border-emerald-300' : 'bg-purple-50/50 border-dashed border-purple-200'}`}>
                    {!activeOrder.otpCode ? (
                      <div className="space-y-3">
                        <div className="w-12 h-12 rounded-full bg-purple-100 text-purple-700 flex items-center justify-center mx-auto">
                          <RefreshCw className="w-6 h-6 animate-spin" />
                        </div>
                        <h3 className="text-base font-bold text-slate-900">Waiting for provider SMS code...</h3>
                        <p className="text-xs text-slate-500 max-w-sm mx-auto">Enter the phone number into {activeOrder.serviceName}. The server checks the provider node every 3 seconds.</p>
                      </div>
                    ) : (
                      <div className="space-y-3">
                        <span className="text-xs font-bold text-emerald-800 uppercase tracking-widest block">SMS Code Received</span>
                        <div className="text-5xl font-extrabold font-mono tracking-widest text-emerald-700 tabular-nums py-2 select-all">
                          {activeOrder.otpCode}
                        </div>
                        <button 
                          onClick={() => copyToClipboard(activeOrder.otpCode!)}
                          className="px-6 py-2.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-xs transition-colors inline-flex items-center gap-2"
                        >
                          <Copy className="w-4 h-4" />
                          <span>Copy OTP Code</span>
                        </button>
                      </div>
                    )}
                  </div>

                  {/* Actions & Refund Guarantee */}
                  <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                    <span className="text-xs text-slate-400">Order Cost: ${activeOrder.price.toFixed(2)}</span>
                    {activeOrder.status === 'WAITING' && (
                      <button 
                        onClick={() => handleCancelOrder(activeOrder)}
                        className="px-4 py-2 text-xs font-bold text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-200 rounded-xl transition-colors"
                      >
                        Cancel & Instant Refund
                      </button>
                    )}
                  </div>

                </div>

                {/* Refund Notice */}
                <div className="text-xs text-slate-500 bg-white border border-slate-200 rounded-xl p-4 flex items-start gap-3 shadow-xs">
                  <Shield className="w-5 h-5 text-purple-700 shrink-0 mt-0.5" />
                  <div>
                    <strong className="text-slate-800">100% Unused Number Refund Guarantee:</strong> If no SMS is delivered within 15 minutes, the order closes automatically and ${activeOrder.price.toFixed(2)} is credited back to your wallet ledger.
                  </div>
                </div>

              </div>
            )}

            {/* TAB 3: ORDERS HISTORY */}
            {userTab === 'orders' && (
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h2 className="text-xl font-bold text-slate-900">Activation Orders History</h2>
                  <button onClick={() => setUserTab('services')} className="px-3.5 py-1.5 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-lg">
                    Buy Another Number
                  </button>
                </div>

                <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                      <tr>
                        <th className="px-5 py-3.5">Order Number</th>
                        <th className="px-5 py-3.5">Service</th>
                        <th className="px-5 py-3.5">Assigned Phone</th>
                        <th className="px-5 py-3.5">OTP Code</th>
                        <th className="px-5 py-3.5">Status</th>
                        <th className="px-5 py-3.5 text-right">Price</th>
                        <th className="px-5 py-3.5 text-right">Action</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 text-xs">
                      {orders.map(ord => (
                        <tr key={ord.id} className="hover:bg-slate-50">
                          <td className="px-5 py-3.5 font-mono font-bold text-slate-700">
                            {ord.orderNumber}
                            <span className="block text-[10px] text-slate-400 font-normal">{ord.createdAt}</span>
                          </td>
                          <td className="px-5 py-3.5 font-semibold text-slate-900">
                            {ord.serviceName}
                            <span className="block text-[11px] text-slate-400">{ord.countryName}</span>
                          </td>
                          <td className="px-5 py-3.5 font-mono font-semibold text-slate-800 tabular-nums">
                            +{ord.phoneNumber}
                          </td>
                          <td className="px-5 py-3.5">
                            {ord.otpCode ? (
                              <span className="font-mono tabular-nums font-extrabold text-emerald-700 bg-emerald-50 px-2.5 py-1 rounded-md border border-emerald-200 text-xs">
                                {ord.otpCode}
                              </span>
                            ) : (
                              <span className="text-slate-400">—</span>
                            )}
                          </td>
                          <td className="px-5 py-3.5">
                            <span className={`inline-block px-2.5 py-0.5 rounded text-[10px] font-mono font-bold ${
                              ord.status === 'RECEIVED' ? 'bg-emerald-50 text-emerald-700' :
                              ord.status === 'WAITING' ? 'bg-amber-50 text-amber-700' :
                              'bg-slate-100 text-slate-600'
                            }`}>
                              {ord.status}
                            </span>
                          </td>
                          <td className="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-slate-900">
                            ${ord.price.toFixed(2)}
                          </td>
                          <td className="px-5 py-3.5 text-right">
                            <button 
                              onClick={() => { setActiveOrderId(ord.id); setUserTab('order'); }}
                              className="text-xs font-semibold text-purple-700 hover:text-purple-900"
                            >
                              View Monitor →
                            </button>
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* TAB 4: WALLET & PAYMENT GATEWAYS */}
            {userTab === 'wallet' && (
              <div className="space-y-8">
                
                {/* Balance Summary Header */}
                <div className="bg-white border border-purple-200 rounded-2xl p-6 sm:p-8 shadow-xs flex flex-col md:flex-row md:items-center justify-between gap-6">
                  <div>
                    <span className="text-xs font-bold text-purple-700 uppercase tracking-wider block">Wallet Balance Ledger</span>
                    <div className="text-4xl font-extrabold font-mono tabular-nums text-slate-900 mt-1">
                      ${walletBalance.toFixed(2)}
                    </div>
                    <p className="text-xs text-slate-500 mt-1">Immutable ledger accounting with atomic database transactions.</p>
                  </div>

                  <div className="flex items-center gap-3">
                    <button onClick={() => setUserTab('services')} className="px-4 py-2.5 text-xs font-semibold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs">
                      Purchase Numbers
                    </button>
                  </div>
                </div>

                {/* Gateways Grid */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                  
                  {/* Gateway 1: Razorpay Checkout */}
                  <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div>
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <CreditCard className="w-5 h-5 text-purple-700" />
                          <h3 className="text-base font-bold text-slate-900">Razorpay Online Gateway</h3>
                        </div>
                        <span className="text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                          INSTANT
                        </span>
                      </div>
                      <p className="text-xs text-slate-500 mt-1">Cards, UPI, Netbanking. Real-time HMAC-SHA256 signature verification.</p>

                      <div className="mt-4 space-y-3">
                        <label className="block text-xs font-semibold text-slate-700">Quick Recharge Presets</label>
                        <div className="grid grid-cols-4 gap-2">
                          {[5, 10, 25, 50].map(amt => (
                            <button
                              key={amt}
                              onClick={() => handleRazorpayRecharge(amt)}
                              className="py-2 text-xs font-bold font-mono bg-purple-50 hover:bg-purple-100 text-purple-700 rounded-xl border border-purple-200 transition-colors"
                            >
                              +${amt}
                            </button>
                          ))}
                        </div>
                      </div>
                    </div>

                    <div className="pt-4 border-t border-slate-100 flex items-center justify-between text-xs text-slate-500">
                      <span>Supported: Visa, Mastercard, RuPay, UPI</span>
                      <span className="font-semibold text-purple-700">Test Simulator Enabled</span>
                    </div>
                  </div>

                  {/* Gateway 2: Manual UPI / QR Gateway */}
                  <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col justify-between space-y-4">
                    <div>
                      <div className="flex items-center justify-between">
                        <div className="flex items-center gap-2">
                          <QrCode className="w-5 h-5 text-purple-700" />
                          <h3 className="text-base font-bold text-slate-900">Manual UPI / QR Code</h3>
                        </div>
                        <span className="text-[10px] font-bold text-amber-700 bg-amber-50 px-2 py-0.5 rounded border border-amber-200">
                          ADMIN REVIEW
                        </span>
                      </div>
                      <p className="text-xs text-slate-500 mt-1">Scan QR or transfer to UPI ID, then submit transaction reference (UTR).</p>

                      <form onSubmit={handleManualSubmit} className="mt-4 space-y-3">
                        <div className="grid grid-cols-2 gap-3">
                          <div>
                            <label className="block text-xs font-semibold text-slate-700">Amount ($)</label>
                            <input 
                              type="number" 
                              value={manualAmt} 
                              onChange={(e) => setManualAmt(e.target.value)} 
                              className="w-full mt-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono font-bold"
                            />
                          </div>
                          <div>
                            <label className="block text-xs font-semibold text-slate-700">UPI ID</label>
                            <div className="mt-1 px-3 py-1.5 bg-slate-100 rounded-lg text-xs font-mono text-slate-800 select-all font-semibold">
                              pay@otpdoctor
                            </div>
                          </div>
                        </div>

                        <div>
                          <label className="block text-xs font-semibold text-slate-700">UTR / Reference ID</label>
                          <input 
                            type="text" 
                            value={manualUtr} 
                            onChange={(e) => setManualUtr(e.target.value)} 
                            placeholder="e.g. 402918471928"
                            className="w-full mt-1 px-3 py-1.5 bg-slate-50 border border-slate-200 rounded-lg text-xs font-mono"
                          />
                        </div>

                        <button 
                          type="submit" 
                          className="w-full py-2 text-xs font-bold text-white bg-slate-900 hover:bg-slate-800 rounded-xl transition-colors"
                        >
                          Submit Proof for Review
                        </button>
                      </form>
                    </div>
                  </div>

                </div>

                {/* Ledger Transactions Table */}
                <div className="space-y-3">
                  <h3 className="text-base font-bold text-slate-900">Immutable Ledger Entries</h3>
                  <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                    <table className="w-full text-left text-xs">
                      <thead className="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                        <tr>
                          <th className="px-5 py-3">Timestamp</th>
                          <th className="px-5 py-3">Type</th>
                          <th className="px-5 py-3">Description</th>
                          <th className="px-5 py-3">Reference</th>
                          <th className="px-5 py-3 text-right">Amount</th>
                          <th className="px-5 py-3 text-right">Balance After</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100">
                        {transactions.map(tx => (
                          <tr key={tx.id} className="hover:bg-slate-50">
                            <td className="px-5 py-3 text-slate-500 font-mono">{tx.date}</td>
                            <td className="px-5 py-3 font-semibold uppercase">
                              <span className={`inline-block px-2 py-0.5 rounded text-[10px] ${
                                tx.type === 'credit' ? 'bg-emerald-50 text-emerald-700' :
                                tx.type === 'refund' ? 'bg-purple-50 text-purple-700' :
                                tx.type === 'debit' ? 'bg-rose-50 text-rose-700' :
                                'bg-amber-50 text-amber-700'
                              }`}>
                                {tx.type}
                              </span>
                            </td>
                            <td className="px-5 py-3 text-slate-800 font-medium">{tx.description}</td>
                            <td className="px-5 py-3 font-mono text-slate-500 text-[11px]">{tx.reference}</td>
                            <td className={`px-5 py-3 text-right font-mono tabular-nums font-bold ${tx.type === 'credit' || tx.type === 'refund' ? 'text-emerald-600' : 'text-slate-800'}`}>
                              {tx.type === 'credit' || tx.type === 'refund' ? '+' : '-'}${tx.amount.toFixed(2)}
                            </td>
                            <td className="px-5 py-3 text-right font-mono tabular-nums font-bold text-slate-600">
                              ${tx.balanceAfter.toFixed(2)}
                            </td>
                          </tr>
                        ))}
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            )}

          </div>
        )}

        {/* =========================================================================
            SUPER ADMIN CONSOLE PORTAL (Strictly One Super Admin)
            ========================================================================= */}
        {activePortal === 'admin' && (
          <div className="space-y-8">
            
            {/* Admin Header with Breadcrumb & Single Super Admin marker */}
            <div className="flex flex-col sm:flex-row sm:items-center justify-between pb-4 border-b border-slate-200 gap-4">
              <div>
                <div className="flex items-center gap-2">
                  <h1 className="text-2xl font-bold tracking-tight text-slate-900">Super Admin Console</h1>
                  <span className="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">
                    SINGLE ADMIN RULE VERIFIED
                  </span>
                </div>
                <p className="text-sm text-slate-500">Oversee orders, external provider synchronization, custom pricing, and payment verification.</p>
              </div>

              {/* Sub-Nav Controls */}
              <div className="flex items-center gap-1.5 bg-slate-100 p-1 rounded-xl text-xs font-semibold overflow-x-auto">
                <button 
                  onClick={() => setAdminTab('dashboard')}
                  className={`px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors ${adminTab === 'dashboard' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Overview
                </button>
                <button 
                  onClick={() => setAdminTab('providers')}
                  className={`px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors ${adminTab === 'providers' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Provider API & Sync
                </button>
                <button 
                  onClick={() => setAdminTab('services')}
                  className={`px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors ${adminTab === 'services' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Services & Margins
                </button>
                <button 
                  onClick={() => setAdminTab('orders')}
                  className={`px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors ${adminTab === 'orders' ? 'bg-white text-slate-900 shadow-xs' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Orders Audit
                </button>
                <button 
                  onClick={() => setAdminTab('payments')}
                  className={`px-3 py-1.5 rounded-lg whitespace-nowrap transition-colors ${adminTab === 'payments' ? 'bg-white text-purple-700 shadow-xs font-bold' : 'text-slate-600 hover:text-slate-900'}`}
                >
                  Manual QR Queue ({manualRequests.filter(m => m.status === 'PENDING').length})
                </button>
              </div>
            </div>

            {/* ADMIN TAB 1: OVERVIEW METRICS */}
            {adminTab === 'dashboard' && (
              <div className="space-y-6">
                
                {/* 4 Metric Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                  <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Platform Users</span>
                    <div className="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-1">1,420</div>
                    <span className="text-[11px] text-slate-400 mt-1 block">Active client accounts</span>
                  </div>

                  <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total OTP Orders</span>
                    <div className="text-2xl font-bold font-mono tabular-nums text-slate-900 mt-1">{orders.length + 842}</div>
                    <span className="text-[11px] text-emerald-600 mt-1 block">89% Delivered Rate</span>
                  </div>

                  <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Total Revenue</span>
                    <div className="text-2xl font-bold font-mono tabular-nums text-purple-700 mt-1">$4,820.50</div>
                    <span className="text-[11px] text-slate-400 mt-1 block">Razorpay & Manual QR</span>
                  </div>

                  <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs">
                    <span className="text-xs font-semibold text-slate-500 uppercase tracking-wider block">Provider Balance (OTP Doctor)</span>
                    <div className="text-2xl font-bold font-mono tabular-nums text-emerald-600 mt-1">{providerBalance.toFixed(2)} RUB</div>
                    <span className="text-[11px] text-slate-400 mt-1 block">Active API supplier balance</span>
                  </div>
                </div>

                {/* Quick Actions Strip */}
                <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs flex flex-col md:flex-row items-center justify-between gap-4">
                  <div>
                    <h3 className="text-sm font-bold text-slate-900">OTP Doctor External Integration</h3>
                    <p className="text-xs text-slate-500 font-mono mt-0.5">https://www.otpdoctor.in/stubs/handler_api.php</p>
                  </div>
                  <div className="flex items-center gap-3">
                    <button 
                      onClick={handleTestProvider}
                      disabled={providerTesting}
                      className="px-4 py-2 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors flex items-center gap-2"
                    >
                      <RefreshCw className={`w-3.5 h-3.5 ${providerTesting ? 'animate-spin' : ''}`} />
                      <span>Test Handshake</span>
                    </button>
                    <button 
                      onClick={() => setAdminTab('payments')}
                      className="px-4 py-2 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                    >
                      Review Manual Payments ({manualRequests.filter(m => m.status === 'PENDING').length})
                    </button>
                  </div>
                </div>

                {providerTestMsg && (
                  <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-medium text-emerald-800">
                    {providerTestMsg}
                  </div>
                )}

              </div>
            )}

            {/* ADMIN TAB 2: PROVIDER API & SYNC */}
            {adminTab === 'providers' && (
              <div className="space-y-6">
                
                <div className="bg-white border border-slate-200 rounded-2xl p-6 shadow-xs space-y-4">
                  <div className="flex items-center justify-between">
                    <div>
                      <h2 className="text-base font-bold text-slate-900">Configured SMS Provider: OTP Doctor</h2>
                      <span className="text-xs text-slate-400 font-mono">Handler API Integration</span>
                    </div>
                    <button 
                      onClick={handleTestProvider}
                      className="px-4 py-2 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                    >
                      Test API Connection
                    </button>
                  </div>

                  <div className="grid grid-cols-1 md:grid-cols-2 gap-4 text-xs font-mono">
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-200">
                      <span className="text-slate-500 font-sans block text-[11px]">Handler Base URL</span>
                      <span className="text-slate-900 font-bold select-all break-all">https://www.otpdoctor.in/stubs/handler_api.php</span>
                    </div>
                    <div className="p-3 bg-slate-50 rounded-xl border border-slate-200">
                      <span className="text-slate-500 font-sans block text-[11px]">API Key / Secret Token</span>
                      <span className="text-slate-900 font-bold">•••••••••••••••••••• (Redacted in all public views)</span>
                    </div>
                  </div>

                  {providerTestMsg && (
                    <div className="p-3 bg-emerald-50 border border-emerald-200 rounded-xl text-xs font-medium text-emerald-800">
                      {providerTestMsg}
                    </div>
                  )}
                </div>

                {/* Sanitized Outbound Provider Request Logs */}
                <div className="space-y-3">
                  <h3 className="text-base font-bold text-slate-900">Sanitized Provider API Logs</h3>
                  <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                    <table className="w-full text-left text-xs font-mono">
                      <thead className="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600 font-sans">
                        <tr>
                          <th className="px-5 py-3">Time</th>
                          <th className="px-5 py-3">Action</th>
                          <th className="px-5 py-3">Sanitized URL</th>
                          <th className="px-5 py-3">HTTP</th>
                          <th className="px-5 py-3">Latency</th>
                          <th className="px-5 py-3">Status Response</th>
                        </tr>
                      </thead>
                      <tbody className="divide-y divide-slate-100 text-[11px]">
                        <tr className="hover:bg-slate-50">
                          <td className="px-5 py-2.5 text-slate-500">09:45:12</td>
                          <td className="px-5 py-2.5 font-bold text-purple-700">getNumber</td>
                          <td className="px-5 py-2.5 text-slate-600 max-w-xs truncate">/stubs/handler_api.php?action=getNumber&service=wa&country=in</td>
                          <td className="px-5 py-2.5 font-bold text-emerald-600">200</td>
                          <td className="px-5 py-2.5 text-slate-500">240ms</td>
                          <td className="px-5 py-2.5 text-slate-900 font-bold">ACCESS_NUMBER:94827104:919876543210</td>
                        </tr>
                        <tr className="hover:bg-slate-50">
                          <td className="px-5 py-2.5 text-slate-500">09:45:18</td>
                          <td className="px-5 py-2.5 font-bold text-purple-700">getStatus</td>
                          <td className="px-5 py-2.5 text-slate-600 max-w-xs truncate">/stubs/handler_api.php?action=getStatus&id=94827104</td>
                          <td className="px-5 py-2.5 font-bold text-emerald-600">200</td>
                          <td className="px-5 py-2.5 text-slate-500">180ms</td>
                          <td className="px-5 py-2.5 text-slate-900 font-bold">STATUS_OK:849204</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
            )}

            {/* ADMIN TAB 3: SERVICES & PRICING */}
            {adminTab === 'services' && (
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <div>
                    <h2 className="text-base font-bold text-slate-900">Custom Selling Prices & Profit Margins</h2>
                    <p className="text-xs text-slate-500">Independent price customization preserved across catalog synchronizations.</p>
                  </div>
                </div>

                <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                      <tr>
                        <th className="px-5 py-3.5">Code</th>
                        <th className="px-5 py-3.5">Service Name</th>
                        <th className="px-5 py-3.5">Provider Cost</th>
                        <th className="px-5 py-3.5">Selling Price</th>
                        <th className="px-5 py-3.5">Gross Margin</th>
                        <th className="px-5 py-3.5">Status</th>
                        <th className="px-5 py-3.5 text-right">Action</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 text-xs">
                      {services.map(srv => {
                        const margin = ((srv.price - srv.providerCost) / srv.price) * 100;
                        return (
                          <tr key={srv.id} className="hover:bg-slate-50">
                            <td className="px-5 py-3 font-mono font-bold text-slate-700">{srv.code}</td>
                            <td className="px-5 py-3 font-bold text-slate-900">{srv.name}</td>
                            <td className="px-5 py-3 font-mono tabular-nums text-slate-500">${srv.providerCost.toFixed(2)}</td>
                            <td className="px-5 py-3 font-mono tabular-nums font-bold text-purple-700">
                              <input 
                                type="number" 
                                step="0.05"
                                value={srv.price}
                                onChange={(e) => {
                                  const val = parseFloat(e.target.value) || srv.price;
                                  setServices(prev => prev.map(s => s.id === srv.id ? { ...s, price: val } : s));
                                }}
                                className="w-20 px-2 py-1 bg-slate-50 border border-slate-200 rounded font-mono font-bold text-xs"
                              />
                            </td>
                            <td className="px-5 py-3 font-mono font-semibold text-emerald-600">
                              {margin.toFixed(0)}% (${(srv.price - srv.providerCost).toFixed(2)})
                            </td>
                            <td className="px-5 py-3">
                              <span className="text-[11px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded">
                                ACTIVE
                              </span>
                            </td>
                            <td className="px-5 py-3 text-right">
                              <button 
                                onClick={() => showToast(`Price saved for ${srv.name}: $${srv.price.toFixed(2)}`)}
                                className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-slate-800 font-bold rounded text-xs transition-colors"
                              >
                                Save
                              </button>
                            </td>
                          </tr>
                        );
                      })}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* ADMIN TAB 4: ORDERS AUDIT */}
            {adminTab === 'orders' && (
              <div className="space-y-4">
                <h2 className="text-base font-bold text-slate-900">Global Activation Logs</h2>
                <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                  <table className="w-full text-left text-xs">
                    <thead className="bg-slate-50 border-b border-slate-200 font-semibold text-slate-600">
                      <tr>
                        <th className="px-5 py-3">Order Number</th>
                        <th className="px-5 py-3">Service</th>
                        <th className="px-5 py-3">Phone</th>
                        <th className="px-5 py-3">Activation ID</th>
                        <th className="px-5 py-3">OTP Code</th>
                        <th className="px-5 py-3">Status</th>
                        <th className="px-5 py-3 text-right">Price</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100">
                      {orders.map(ord => (
                        <tr key={ord.id} className="hover:bg-slate-50">
                          <td className="px-5 py-3 font-mono font-bold text-slate-700">{ord.orderNumber}</td>
                          <td className="px-5 py-3 font-semibold text-slate-900">{ord.serviceName}</td>
                          <td className="px-5 py-3 font-mono tabular-nums font-semibold text-slate-900">+{ord.phoneNumber}</td>
                          <td className="px-5 py-3 font-mono text-slate-500">{ord.activationId}</td>
                          <td className="px-5 py-3 font-mono font-bold text-emerald-700">{ord.otpCode || '—'}</td>
                          <td className="px-5 py-3 font-mono font-bold">{ord.status}</td>
                          <td className="px-5 py-3 text-right font-mono tabular-nums font-bold text-slate-900">${ord.price.toFixed(2)}</td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

            {/* ADMIN TAB 5: MANUAL QR REVIEW QUEUE */}
            {adminTab === 'payments' && (
              <div className="space-y-4">
                <div className="flex items-center justify-between">
                  <h2 className="text-base font-bold text-slate-900">Manual Payment Verification Queue</h2>
                  <span className="text-xs font-semibold text-slate-500">
                    Never trust client claims alone. Verify bank / UPI statement before approval.
                  </span>
                </div>

                <div className="bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-xs">
                  <table className="w-full text-left text-sm">
                    <thead className="bg-slate-50 border-b border-slate-200 text-xs font-semibold text-slate-600">
                      <tr>
                        <th className="px-5 py-3.5">Date</th>
                        <th className="px-5 py-3.5">User</th>
                        <th className="px-5 py-3.5">Claimed Reference (UTR)</th>
                        <th className="px-5 py-3.5 text-right">Amount</th>
                        <th className="px-5 py-3.5">Status</th>
                        <th className="px-5 py-3.5 text-right">Action</th>
                      </tr>
                    </thead>
                    <tbody className="divide-y divide-slate-100 text-xs">
                      {manualRequests.map(req => (
                        <tr key={req.id} className="hover:bg-slate-50">
                          <td className="px-5 py-3.5 text-slate-500 font-mono">{req.date}</td>
                          <td className="px-5 py-3.5">
                            <span className="font-bold text-slate-900 block">{req.userName}</span>
                            <span className="text-slate-400 font-mono text-[11px]">{req.userEmail}</span>
                          </td>
                          <td className="px-5 py-3.5 font-mono font-bold text-slate-800 select-all">{req.reference}</td>
                          <td className="px-5 py-3.5 text-right font-mono tabular-nums font-bold text-purple-700 text-sm">
                            ${req.amount.toFixed(2)}
                          </td>
                          <td className="px-5 py-3.5">
                            <span className={`inline-block px-2.5 py-0.5 rounded text-[10px] font-bold ${
                              req.status === 'PENDING' ? 'bg-amber-50 text-amber-700 border border-amber-200' :
                              req.status === 'APPROVED' ? 'bg-emerald-50 text-emerald-700' :
                              'bg-rose-50 text-rose-700'
                            }`}>
                              {req.status}
                            </span>
                          </td>
                          <td className="px-5 py-3.5 text-right space-x-2">
                            {req.status === 'PENDING' ? (
                              <>
                                <button 
                                  onClick={() => handleApproveManual(req)}
                                  className="px-3 py-1 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-lg text-xs transition-colors shadow-xs"
                                >
                                  Approve & Credit
                                </button>
                                <button 
                                  onClick={() => handleRejectManual(req)}
                                  className="px-3 py-1 bg-slate-100 hover:bg-slate-200 text-rose-700 font-bold rounded-lg text-xs transition-colors"
                                >
                                  Reject
                                </button>
                              </>
                            ) : (
                              <span className="text-slate-400 font-medium">Processed</span>
                            )}
                          </td>
                        </tr>
                      ))}
                    </tbody>
                  </table>
                </div>
              </div>
            )}

          </div>
        )}

        {/* =========================================================================
            WEB-BASED INSTALLATION WIZARD PORTAL (/install)
            ========================================================================= */}
        {activePortal === 'installer' && (
          <div className="space-y-6 max-w-4xl mx-auto">
            
            {/* Installer Simulation & Lock Controls */}
            <div className="bg-purple-50 border border-purple-200 rounded-2xl p-4 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs">
              <div className="flex items-center gap-2">
                <Terminal className="w-4 h-4 text-purple-700" />
                <span className="font-semibold text-purple-900">Endpoint: <code className="font-mono bg-white px-2 py-0.5 rounded border border-purple-200">https://yourdomain.com/install</code></span>
                <span aria-hidden="true" className="text-purple-300">·</span>
                <span className="text-purple-700">Lock File: <code className="font-mono">storage/installed.lock</code></span>
              </div>
              <div className="flex items-center gap-2">
                <span className="text-slate-500">Simulate State:</span>
                <button 
                  onClick={() => setIsSystemInstalled(!isSystemInstalled)}
                  className={`px-3 py-1 rounded-lg font-bold text-xs transition-colors ${isSystemInstalled ? 'bg-purple-700 text-white' : 'bg-slate-200 text-slate-700 hover:bg-slate-300'}`}
                >
                  {isSystemInstalled ? 'Locked (Already Installed)' : 'Unlocked (Ready to Install)'}
                </button>
              </div>
            </div>

            {/* When System is Already Installed and Locked */}
            {isSystemInstalled && installerStep !== 8 ? (
              <div className="bg-white border border-purple-200 rounded-2xl p-8 sm:p-12 shadow-sm text-center space-y-5 max-w-xl mx-auto">
                <div className="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
                  <Shield className="w-8 h-8" />
                </div>
                <div className="space-y-1.5">
                  <h2 className="text-2xl font-bold tracking-tight text-slate-900">Application Already Installed</h2>
                  <p className="text-xs text-slate-500">
                    The platform has already been initialized and permanently locked with <code className="font-mono bg-slate-100 px-1.5 py-0.5 rounded text-purple-700 font-bold">storage/installed.lock</code> to prevent accidental database overwriting.
                  </p>
                </div>

                <div className="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-600 text-left space-y-1">
                  <div>✔ Database tables & indexes active</div>
                  <div>✔ Single Super Admin account provisioned</div>
                  <div>✔ Reinstallation blocked by web server</div>
                </div>

                <div className="pt-2 flex flex-col sm:flex-row items-center justify-center gap-3">
                  <button 
                    onClick={() => { setActivePortal('admin'); setAdminTab('dashboard'); }}
                    className="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                  >
                    Go to Super Admin Panel
                  </button>
                  <button 
                    onClick={() => { setActivePortal('user'); setUserTab('services'); }}
                    className="w-full sm:w-auto px-5 py-2.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                  >
                    Go to User Marketplace
                  </button>
                </div>
                
                <div className="pt-2">
                  <button 
                    onClick={() => { setIsSystemInstalled(false); setInstallerStep(1); }}
                    className="text-[11px] text-purple-600 hover:underline font-semibold"
                  >
                    Click to test run the 8-Step Installation Wizard again →
                  </button>
                </div>
              </div>
            ) : (

              /* The Full 8-Step Wizard */
              <div className="space-y-6">

                {/* 8-Step Progress Indicator */}
                <div className="grid grid-cols-4 sm:grid-cols-8 gap-2 text-center text-[10px] font-bold">
                  {['1. Welcome', '2. Requirements', '3. Database', '4. Website', '5. Admin', '6. Install', '7. Config', '8. Complete'].map((label, idx) => {
                    const stepNum = idx + 1;
                    const isDone = installerStep > stepNum;
                    const isCurrent = installerStep === stepNum;
                    return (
                      <div key={label} className="space-y-1">
                        <div className={`h-1.5 rounded-full transition-all ${isDone ? 'bg-purple-700' : isCurrent ? 'bg-purple-500 animate-pulse' : 'bg-slate-200'}`} />
                        <span className={`block truncate ${isCurrent ? 'text-purple-700 font-extrabold' : isDone ? 'text-slate-800' : 'text-slate-400'}`}>
                          {label}
                        </span>
                      </div>
                    );
                  })}
                </div>

                <div className="bg-white border border-purple-200 rounded-2xl p-6 sm:p-10 shadow-sm space-y-6">

                  {/* STEP 1: WELCOME PAGE */}
                  {installerStep === 1 && (
                    <div className="space-y-6 text-center py-4">
                      <div className="w-20 h-20 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center font-extrabold text-3xl mx-auto shadow-sm">
                        OD
                      </div>
                      <div className="space-y-2">
                        <h2 className="text-2xl font-bold tracking-tight text-slate-900">Welcome to OTP Doctor Web Installer</h2>
                        <p className="text-xs text-slate-500 max-w-md mx-auto">
                          This wizard sets up your MySQL database, single Super Admin credentials, and essential settings without requiring command-line tools.
                        </p>
                      </div>

                      <div className="bg-purple-50 border border-purple-200 rounded-xl p-4 text-xs text-purple-900 text-left max-w-md mx-auto space-y-1.5">
                        <div className="font-bold">What will be configured:</div>
                        <ul className="list-disc list-inside space-y-0.5 text-purple-800">
                          <li>Server compatibility and PHP extension verification</li>
                          <li>MySQL PDO connection and automatic database initialization</li>
                          <li>Automated schema, indexes, and initial services seeding</li>
                          <li>Single Super Admin account provisioning</li>
                          <li>Permanent installation lock (<code className="font-mono">storage/installed.lock</code>)</li>
                        </ul>
                      </div>

                      <div className="pt-2">
                        <button 
                          onClick={() => setInstallerStep(2)}
                          className="px-8 py-3 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                          Start Installation →
                        </button>
                      </div>
                    </div>
                  )}

                  {/* STEP 2: SERVER REQUIREMENTS */}
                  {installerStep === 2 && (
                    <div className="space-y-6">
                      <div className="border-b border-slate-100 pb-4">
                        <h2 className="text-xl font-bold text-slate-900">Step 2: Server Compatibility & Requirements</h2>
                        <p className="text-xs text-slate-500">Checking hosting environment, PHP extensions, and folder write permissions.</p>
                      </div>

                      <div className="divide-y divide-slate-100 text-xs">
                        <div className="py-3 flex items-center justify-between">
                          <div>
                            <span className="font-bold text-slate-900 block">PHP Version (&gt;= 8.2.0)</span>
                            <span className="text-slate-400 font-mono">Environment: PHP 8.3.6 Stable</span>
                          </div>
                          <span className="font-bold px-2.5 py-1 rounded-md text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✔ Compatible
                          </span>
                        </div>

                        {[
                          { name: 'pdo', label: 'PDO (PHP Data Objects)' },
                          { name: 'pdo_mysql', label: 'MySQL PDO Driver' },
                          { name: 'curl', label: 'cURL HTTP Client' },
                          { name: 'mbstring', label: 'Multibyte String' },
                          { name: 'json', label: 'JSON Serializer' },
                          { name: 'openssl', label: 'OpenSSL Security' }
                        ].map(ext => (
                          <div key={ext.name} className="py-2.5 flex items-center justify-between">
                            <span className="font-semibold text-slate-800">{ext.label} (<code className="font-mono text-purple-700 font-bold">{ext.name}</code>)</span>
                            <span className="font-bold px-2 py-0.5 rounded text-[10px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                              ✔ Enabled
                            </span>
                          </div>
                        ))}

                        <div className="py-3 flex items-center justify-between">
                          <div>
                            <span className="font-bold text-slate-900 block">Directory Permissions</span>
                            <span className="text-slate-400 font-mono">config/ & storage/logs/</span>
                          </div>
                          <span className="font-bold px-2.5 py-1 rounded-md text-[11px] bg-emerald-50 text-emerald-700 border border-emerald-200">
                            ✔ Writable (0755)
                          </span>
                        </div>
                      </div>

                      <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button onClick={() => setInstallerStep(1)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                          onClick={() => setInstallerStep(3)}
                          className="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                          Next: Database Configuration →
                        </button>
                      </div>
                    </div>
                  )}

                  {/* STEP 3: DATABASE CONFIGURATION */}
                  {installerStep === 3 && (
                    <div className="space-y-6">
                      <div className="border-b border-slate-100 pb-4">
                        <h2 className="text-xl font-bold text-slate-900">Step 3: Database Configuration</h2>
                        <p className="text-xs text-slate-500">Provide your MySQL database credentials. We test the connection before proceeding.</p>
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                        <div className="sm:col-span-2">
                          <label className="block text-xs font-semibold text-slate-700">MySQL Host</label>
                          <input 
                            type="text" 
                            value={installDbHost} 
                            onChange={(e) => setInstallDbHost(e.target.value)}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                            placeholder="127.0.0.1 or localhost"
                          />
                        </div>
                        <div>
                          <label className="block text-xs font-semibold text-slate-700">Port</label>
                          <input 
                            type="number" 
                            value={installDbPort} 
                            onChange={(e) => setInstallDbPort(Number(e.target.value))}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                            placeholder="3306"
                          />
                        </div>
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Database Name</label>
                        <input 
                          type="text" 
                          value={installDbName} 
                          onChange={(e) => setInstallDbName(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                          placeholder="otp_platform"
                        />
                        <span className="text-[11px] text-slate-400 mt-1 block">If the database doesn't exist, the installer will attempt to create it automatically.</span>
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-xs font-semibold text-slate-700">Database Username</label>
                          <input 
                            type="text" 
                            value={installDbUser} 
                            onChange={(e) => setInstallDbUser(e.target.value)}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                            placeholder="root"
                          />
                        </div>
                        <div>
                          <label className="block text-xs font-semibold text-slate-700">Database Password</label>
                          <input 
                            type="password" 
                            value={installDbPass} 
                            onChange={(e) => setInstallDbPass(e.target.value)}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono" 
                            placeholder="••••••••"
                          />
                        </div>
                      </div>

                      {installDbMsg && (
                        <div className={`p-3 rounded-xl text-xs font-medium ${installDbTested ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'}`}>
                          {installDbMsg}
                        </div>
                      )}

                      <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button onClick={() => setInstallerStep(2)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <div className="flex items-center gap-3">
                          <button 
                            onClick={handleTestInstallerDb}
                            disabled={installDbTesting}
                            className="px-4 py-2 text-xs font-bold text-purple-700 bg-purple-50 hover:bg-purple-100 border border-purple-200 rounded-xl transition-colors flex items-center gap-2"
                          >
                            <RefreshCw className={`w-3.5 h-3.5 ${installDbTesting ? 'animate-spin' : ''}`} />
                            <span>{installDbTesting ? 'Testing Handshake...' : 'Test Connection'}</span>
                          </button>
                          <button 
                            onClick={() => {
                              if (!installDbTested) {
                                handleTestInstallerDb();
                              }
                              setInstallerStep(4);
                            }}
                            className="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                          >
                            Next: Website Configuration →
                          </button>
                        </div>
                      </div>
                    </div>
                  )}

                  {/* STEP 4: WEBSITE CONFIGURATION */}
                  {installerStep === 4 && (
                    <div className="space-y-6">
                      <div className="border-b border-slate-100 pb-4">
                        <h2 className="text-xl font-bold text-slate-900">Step 4: Website Configuration</h2>
                        <p className="text-xs text-slate-500">Configure your website name, base URL, timezone, and platform currency.</p>
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Website Branding Name</label>
                        <input 
                          type="text" 
                          value={installSiteName} 
                          onChange={(e) => setInstallSiteName(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold" 
                          placeholder="OTP Doctor Verification"
                        />
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Domain Base URL (Auto-Detected)</label>
                        <input 
                          type="url" 
                          value={installAppUrl} 
                          onChange={(e) => setInstallAppUrl(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                          placeholder="https://yourdomain.com"
                        />
                        <span className="text-[11px] text-slate-400 mt-1 block">Used for self-referential payment webhooks and order status callbacks.</span>
                      </div>

                      <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                          <label className="block text-xs font-semibold text-slate-700">Platform Timezone</label>
                          <select 
                            value={installTimezone}
                            onChange={(e) => setInstallTimezone(e.target.value)}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold"
                          >
                            <option value="UTC">UTC (Universal Coordinated Time)</option>
                            <option value="Asia/Kolkata">Asia/Kolkata (IST)</option>
                            <option value="America/New_York">America/New_York (EST)</option>
                            <option value="Europe/London">Europe/London (GMT)</option>
                          </select>
                        </div>
                        <div>
                          <label className="block text-xs font-semibold text-slate-700">Default Currency</label>
                          <select 
                            value={installCurrency}
                            onChange={(e) => setInstallCurrency(e.target.value)}
                            className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold"
                          >
                            <option value="USD">USD ($)</option>
                            <option value="INR">INR (₹)</option>
                            <option value="EUR">EUR (€)</option>
                          </select>
                        </div>
                      </div>

                      <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button onClick={() => setInstallerStep(3)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                          onClick={() => setInstallerStep(5)}
                          className="px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                          Next: Super Admin Account →
                        </button>
                      </div>
                    </div>
                  )}

                  {/* STEP 5: ADMIN ACCOUNT CREATION */}
                  {installerStep === 5 && (
                    <div className="space-y-6">
                      <div className="border-b border-slate-100 pb-4">
                        <div className="flex items-center gap-2">
                          <h2 className="text-xl font-bold text-slate-900">Step 5: Create Single Super Admin Account</h2>
                          <span className="text-[10px] font-bold text-purple-700 bg-purple-50 px-2 py-0.5 rounded border border-purple-200">EXACTLY ONE SUPER ADMIN</span>
                        </div>
                        <p className="text-xs text-slate-500 mt-1">This account will have exclusive administrative authority. Staff or sub-admin roles are strictly prohibited.</p>
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Administrator Full Name</label>
                        <input 
                          type="text" 
                          value={installAdminName} 
                          onChange={(e) => setInstallAdminName(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-semibold" 
                          placeholder="Super Administrator"
                        />
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Administrator Email Address</label>
                        <input 
                          type="email" 
                          value={installAdminEmail} 
                          onChange={(e) => setInstallAdminEmail(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono font-semibold" 
                          placeholder="admin@otpdoctor.in"
                        />
                      </div>

                      <div>
                        <label className="block text-xs font-semibold text-slate-700">Administrator Password (Min 8 Characters)</label>
                        <input 
                          type="password" 
                          value={installAdminPass} 
                          onChange={(e) => setInstallAdminPass(e.target.value)}
                          className="mt-1 w-full px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs font-mono" 
                          placeholder="••••••••••••••••"
                        />
                      </div>

                      <div className="flex items-center justify-between pt-4 border-t border-slate-100">
                        <button onClick={() => setInstallerStep(4)} className="px-4 py-2 text-xs font-semibold text-slate-600 hover:bg-slate-100 rounded-lg">← Back</button>
                        <button 
                          onClick={handleRunInstallerExecution}
                          disabled={installingProgress}
                          className="px-8 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors flex items-center gap-2"
                        >
                          <Terminal className="w-3.5 h-3.5" />
                          <span>Install Tables & Complete Setup →</span>
                        </button>
                      </div>
                    </div>
                  )}

                  {/* STEP 6 & 7: EXECUTING DATABASE INSTALLATION & CONFIG */}
                  {installerStep === 6 && (
                    <div className="space-y-6 text-center py-6">
                      <div className="w-16 h-16 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center mx-auto">
                        <RefreshCw className="w-8 h-8 animate-spin" />
                      </div>
                      <div>
                        <h2 className="text-xl font-bold text-slate-900">Setting up Database & System</h2>
                        <p className="text-xs text-slate-500 mt-1">Executing MySQL schema, performance indexes, service mappings, and locking installer...</p>
                      </div>

                      <div className="max-w-md mx-auto text-left font-mono text-[11px] p-4 bg-slate-900 text-slate-300 rounded-xl space-y-1.5 shadow-md">
                        {installLogs.map((log, lIdx) => (
                          <div key={lIdx} className="text-emerald-400">
                            {log}
                          </div>
                        ))}
                      </div>
                    </div>
                  )}

                  {/* STEP 8: INSTALLATION COMPLETE */}
                  {installerStep === 8 && (
                    <div className="space-y-6 text-center py-4">
                      <div className="w-16 h-16 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center mx-auto shadow-xs">
                        <CheckCircle2 className="w-8 h-8" />
                      </div>

                      <div className="space-y-2">
                        <h2 className="text-2xl font-bold tracking-tight text-slate-900">Installation Successfully Completed!</h2>
                        <p className="text-xs text-slate-500 max-w-md mx-auto">
                          The database tables have been created, configuration written to <code className="font-mono text-purple-700 bg-purple-50 px-1 rounded font-bold">config/database.php</code>, and the installer permanently locked.
                        </p>
                      </div>

                      <div className="bg-slate-50 border border-slate-200 rounded-xl p-4 text-xs text-slate-700 max-w-md mx-auto text-left space-y-1.5">
                        <div>Super Admin: <strong className="text-slate-900">{installAdminEmail}</strong></div>
                        <div>Website URL: <span className="font-mono text-slate-600">{installAppUrl}</span></div>
                        <div>Lock Status: <strong className="text-emerald-700 font-bold">LOCKED (storage/installed.lock created)</strong></div>
                        <div>Protection: <span className="text-slate-500">Installer disabled; reinstallation prevented</span></div>
                      </div>

                      <div className="pt-4 flex flex-col sm:flex-row items-center justify-center gap-3">
                        <button 
                          onClick={() => { setActivePortal('admin'); setAdminTab('dashboard'); }}
                          className="w-full sm:w-auto px-6 py-2.5 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
                        >
                          Sign In to Super Admin Panel
                        </button>
                        <button 
                          onClick={() => { setActivePortal('user'); setUserTab('services'); }}
                          className="w-full sm:w-auto px-6 py-2.5 text-xs font-bold text-slate-700 bg-slate-100 hover:bg-slate-200 rounded-xl transition-colors"
                        >
                          Visit User Marketplace
                        </button>
                      </div>
                    </div>
                  )}

                </div>

              </div>
            )}

          </div>
        )}

        {/* =========================================================================
            PHP & SQL CODEBASE EXPLORER PORTAL
            ========================================================================= */}
        {activePortal === 'code' && (
          <div className="space-y-6">
            <div className="flex items-center justify-between pb-4 border-b border-slate-200">
              <div>
                <h1 className="text-2xl font-bold tracking-tight text-slate-900">Generated Core PHP & MySQL Files</h1>
                <p className="text-sm text-slate-500">Every required production file has been written to the repository ready for Apache/Nginx deployment.</p>
              </div>
              <button 
                onClick={() => {
                  showToast('All production PHP files, MySQL schemas & Cron scripts ready in repo root!');
                }}
                className="px-4 py-2 text-xs font-bold text-white bg-purple-700 hover:bg-purple-800 rounded-xl shadow-xs transition-colors"
              >
                Repo Ready for Deployment
              </button>
            </div>

            <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
              
              {/* File Tree Index */}
              <div className="bg-white border border-slate-200 rounded-2xl p-5 shadow-xs space-y-4">
                <h3 className="text-xs font-bold text-slate-400 uppercase tracking-wider">Repository File Hierarchy</h3>
                <div className="font-mono text-xs text-slate-700 space-y-1.5 overflow-y-auto max-h-[500px]">
                  <div className="font-bold text-purple-700">/config/</div>
                  <div className="pl-4">app.php</div>
                  <div className="pl-4">database.php</div>

                  <div className="font-bold text-purple-700 pt-1">/database/</div>
                  <div className="pl-4">schema.sql (MySQL 8 InnoDB)</div>
                  <div className="pl-4">indexes.sql (High performance)</div>
                  <div className="pl-4">seed.sql (OTP Doctor seed)</div>
                  <div className="pl-4">seed_super_admin.php (CLI)</div>

                  <div className="font-bold text-purple-700 pt-1">/modules/Providers/</div>
                  <div className="pl-4">ProviderInterface.php</div>
                  <div className="pl-4">OtpDoctorProvider.php</div>

                  <div className="font-bold text-purple-700 pt-1">/modules/Payments/</div>
                  <div className="pl-4">PaymentGatewayInterface.php</div>
                  <div className="pl-4">RazorpayService.php (HMAC)</div>
                  <div className="pl-4">ManualQrService.php</div>

                  <div className="font-bold text-purple-700 pt-1">/app/Services/</div>
                  <div className="pl-4">OrderService.php</div>
                  <div className="pl-4">WalletService.php (Ledger)</div>
                  <div className="pl-4">SyncService.php</div>

                  <div className="font-bold text-purple-700 pt-1">/routes/</div>
                  <div className="pl-4">web.php</div>
                  <div className="pl-4">api.php</div>
                  <div className="pl-4">admin.php</div>

                  <div className="font-bold text-purple-700 pt-1">/cron/</div>
                  <div className="pl-4">check_orders.php (1 min)</div>
                  <div className="pl-4">expire_orders.php (15 min auto-refund)</div>
                  <div className="pl-4">sync_services.php</div>
                  <div className="pl-4">daily_reports.php</div>

                  <div className="font-bold text-purple-700 pt-1">/public/</div>
                  <div className="pl-4">index.php</div>
                  <div className="pl-4">.htaccess</div>
                </div>
              </div>

              {/* Quick File Viewer / Code Inspector */}
              <div className="md:col-span-2 bg-slate-900 text-slate-200 rounded-2xl p-6 font-mono text-xs shadow-xl space-y-4 overflow-hidden">
                <div className="flex items-center justify-between pb-3 border-b border-slate-800">
                  <span className="text-purple-400 font-bold">modules/Providers/OtpDoctorProvider.php (cURL Handler Snippet)</span>
                  <button 
                    onClick={() => copyToClipboard('https://www.otpdoctor.in/stubs/handler_api.php')}
                    className="text-[11px] text-slate-400 hover:text-white"
                  >
                    Copy API URL
                  </button>
                </div>

                <pre className="text-slate-300 overflow-x-auto text-[11px] leading-relaxed">
{`<?php
declare(strict_types=1);

namespace App\\Modules\\Providers;

class OtpDoctorProvider implements ProviderInterface {
    protected string $apiUrl = 'https://www.otpdoctor.in/stubs/handler_api.php';
    protected string $apiKey;

    public function getNumber(string $service, string $country, array $opt = []): array {
        $response = $this->sendRequest([
            'action' => 'getNumber',
            'service' => $service,
            'country' => $country
        ]);
        // ACCESS_NUMBER:$id:$phone
        if (str_starts_with($response['body'], 'ACCESS_NUMBER:')) {
            $parts = explode(':', $response['body']);
            return ['success' => true, 'activation_id' => $parts[1], 'phone' => $parts[2]];
        }
        return ['success' => false, 'message' => $response['body']];
    }
}`}
                </pre>
              </div>

            </div>
          </div>
        )}

      </main>

      {/* Quiet Footer */}
      <footer className="bg-white border-t border-purple-100 py-6 mt-auto">
        <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
          <p>© {new Date().getFullYear()} OTP Doctor SMS Platform. All rights reserved.</p>
          <div className="flex items-center gap-4">
            <span>External Provider: OTP Doctor API</span>
            <span aria-hidden="true">·</span>
            <span>15-Minute Unused Number Auto-Refund</span>
            <span aria-hidden="true">·</span>
            <span>Razorpay & Manual QR</span>
          </div>
        </div>
      </footer>

    </div>
  );
}
