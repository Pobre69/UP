<?php

namespace Controllers;

use App\Config\AppConfig;
use App\Middleware\Security;
use App\Repository\ConcorrenteRepository;
use App\Repository\UsuarioDetalhesRepository;
use App\Repository\UsuarioRepository;
use DataBase\Connection\database;

class SignUpController
{
    public function register(): void
    {
        header('Content-Type: application/json');

        $data = json_decode(file_get_contents('php://input'), true);
        if (!is_array($data)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Dados inválidos']);
            return;
        }

        $fullName = trim((string)($data['fullName'] ?? ''));
        $company = trim((string)($data['company'] ?? ''));
        $email = trim(mb_strtolower((string)($data['email'] ?? '')));
        $password = (string)($data['password'] ?? '');
        $instagram = trim((string)($data['instagram'] ?? ''));
        $segment = trim((string)($data['segment'] ?? ''));
        $city = trim((string)($data['city'] ?? ''));
        $mainGoal = trim((string)($data['mainGoal'] ?? ''));
        $competitors = trim((string)($data['competitors'] ?? ''));
        $driveLink = trim((string)($data['driveLink'] ?? ''));
        $attendant = trim((string)($data['attendant'] ?? ''));
        $planoSelecionado = trim((string)($data['planoSelecionado'] ?? ''));

        if ($fullName === '' || $email === '' || $password === '' || $segment === '' || $city === '' || $mainGoal === '' || $planoSelecionado === '') {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Campos obrigatórios não preenchidos']);
            return;
        }

        if (strlen($password) < 6) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'A senha deve ter no mínimo 6 caracteres']);
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'E-mail inválido']);
            return;
        }

        $paymentLinks = (array) AppConfig::get('payments.links', []);
        if (!array_key_exists($planoSelecionado, $paymentLinks)) {
            http_response_code(400);
            echo json_encode(['success' => false, 'mensagem' => 'Plano inválido']);
            return;
        }

        $usuarioRepo = new UsuarioRepository();
        $detalhesRepo = new UsuarioDetalhesRepository();
        $concorrenteRepo = new ConcorrenteRepository();
        $conn = database::getConnection();

        try {
            $conn->beginTransaction();

            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $companyValue = $company !== '' ? $company : null;
            $localizacaoJson = json_encode(['cidade' => $city], JSON_UNESCAPED_UNICODE);

            $usuarioRepo->add($email, $fullName, $hashedPassword, $companyValue, $planoSelecionado);
            $usuario = $usuarioRepo->getByEmail($email);
            $detalhesRepo->add($email, $mainGoal, $driveLink !== '' ? $driveLink : null, $segment, $instagram !== '' ? $instagram : null, $attendant !== '' ? $attendant : null, $localizacaoJson);

            if ($competitors !== '') {
                $concorrenteRepo->add($email, $competitors);
            }

            $conn->commit();

            Security::storePendingPayment([
                'email' => $email,
                'nome' => $fullName,
                'id' => $usuario['id'] ?? null,
                'planoSelecionado' => $planoSelecionado,
            ]);

            http_response_code(201);
            echo json_encode([
                'success' => true,
                'mensagem' => 'Cadastro realizado com sucesso!',
                'paymentUrl' => $paymentLinks[$planoSelecionado],
                'planoSelecionado' => $planoSelecionado
            ]);
        } catch (\PDOException $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('[SignUp][PDO] ' . $e->getMessage());
            http_response_code(str_contains($e->getMessage(), 'Duplicate entry') ? 409 : 500);
            echo json_encode([
                'success' => false,
                'mensagem' => str_contains($e->getMessage(), 'Duplicate entry') ? 'Este e-mail já está cadastrado' : 'Erro ao realizar cadastro. Tente novamente.'
            ]);
        } catch (\Throwable $e) {
            if ($conn->inTransaction()) {
                $conn->rollBack();
            }
            error_log('[SignUp] ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'mensagem' => 'Erro ao realizar cadastro. Tente novamente.']);
        }
    }
}
