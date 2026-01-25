import express from 'express';
import Stripe from 'stripe';
import cors from 'cors';
import dotenv from 'dotenv';

dotenv.config();

const app = express();
const stripe = new Stripe(process.env.STRIPE_SECRET_KEY);

app.use(cors());
app.use(express.json());

// Planos disponíveis
const planos = {
  basico: {
    name: 'Gestão de Instagram',
    price: 25000, // R$ 250,00 em centavos
    currency: 'brl'
  },
  premium: {
    name: 'Instagram + Tráfego Pago',
    price: 49900, // R$ 499,00 em centavos
    currency: 'brl'
  },
  completo: {
    name: 'Site + Instagram + Tráfego',
    price: 99900, // R$ 999,00 em centavos
    currency: 'brl'
  }
};

// Endpoint para criar sessão de checkout
app.post('/create-checkout-session', async (req, res) => {
  try {
    const { planoId } = req.body;
    const plano = planos[planoId];

    if (!plano) {
      return res.status(400).json({ error: 'Plano não encontrado' });
    }

    const session = await stripe.checkout.sessions.create({
      payment_method_types: ['card', 'pix'],
      line_items: [
        {
          price_data: {
            currency: plano.currency,
            product_data: {
              name: plano.name,
              description: `Plano ${plano.name} - Assinatura mensal`,
            },
            unit_amount: plano.price,
            recurring: {
              interval: 'month',
            },
          },
          quantity: 1,
        },
      ],
      mode: 'subscription',
      success_url: `${req.headers.origin}/sucesso?session_id={CHECKOUT_SESSION_ID}`,
      cancel_url: `${req.headers.origin}/cancelado`,
      locale: 'pt-BR',
      billing_address_collection: 'required',
      customer_creation: 'always',
    });

    res.json({ url: session.url });
  } catch (error) {
    console.error('Erro ao criar sessão:', error);
    res.status(500).json({ error: 'Erro interno do servidor' });
  }
});

// Endpoint para verificar status da sessão
app.get('/checkout-session/:sessionId', async (req, res) => {
  try {
    const { sessionId } = req.params;
    const session = await stripe.checkout.sessions.retrieve(sessionId);
    res.json(session);
  } catch (error) {
    console.error('Erro ao buscar sessão:', error);
    res.status(500).json({ error: 'Erro interno do servidor' });
  }
});

const PORT = process.env.PORT || 3001;
app.listen(PORT, () => {
  console.log(`Servidor rodando na porta ${PORT}`);
  console.log(`Acesse: http://localhost:${PORT}`);
});