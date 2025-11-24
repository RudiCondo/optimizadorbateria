<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Usuario; // Asegúrate de usar el modelo Usuario
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Inicializa el controlador.
     * Define el middleware de JWT para algunas rutas.
     */
    public function __construct()
    {
        // Aplica el middleware 'auth:api' a todos los métodos, excepto 'login' y 'register'.
        // Los métodos 'logout' y 'refresh' necesitan un token válido para funcionar.
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
    }

    /**
     * Registra un nuevo usuario.
     * Por defecto, asigna el rol 'operador'.
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nombre' => 'required|string|max:100',
            'email' => 'required|string|email|max:150|unique:usuarios', // Importante: usar 'usuarios'
            'password' => 'required|string|min:6|confirmed',
            'rol' => 'sometimes|in:admin,operador,tecnico', 
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        $usuario = Usuario::create([
            'nombre' => $request->nombre,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'rol' => $request->rol ?? 'operador', // Asigna 'operador' si no se especifica
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario->only(['id', 'nombre', 'email', 'rol'])
        ], 201);
    }

    /**
     * Obtiene un JWT vía las credenciales dadas.
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'error',
                'errors' => $validator->errors()
            ], 422);
        }

        // Intenta autenticar con el guard 'api' (JWT)
        if (! $token = Auth::guard('api')->attempt($validator->validated())) {
            return response()->json([
                'status' => 'error',
                'message' => 'No autorizado. Credenciales inválidas'
            ], 401);
        }

        return $this->respondWithToken($token);
    }

    /**
     * Cierra la sesión del usuario (invalida el token).
     */
    public function logout()
    {
        Auth::guard('api')->logout();

        return response()->json([
            'status' => 'success',
            'message' => 'Sesión cerrada exitosamente (Token invalidado)'
        ]);
    }

    /**
     * Refresca un token.
     */
    public function refresh()
    {
        // El token anterior se invalida y se genera uno nuevo.
        return $this->respondWithToken(Auth::guard('api')->refresh());
    }

    /**
     * Obtiene el Usuario autenticado (ejemplo de ruta protegida).
     */
    public function me()
    {
        return response()->json([
            'status' => 'success',
            'user' => Auth::guard('api')->user()
        ]);
    }

    /**
     * Obtiene la estructura del token.
     */
    protected function respondWithToken($token)
    {
        return response()->json([
            'status' => 'success',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => Auth::guard('api')->factory()->getTTL() * 60, // Tiempo de expiración en segundos
            // No devolvemos el usuario completo aquí para mantener el payload del token más ligero,
            // pero puedes agregarlo si es necesario.
        ]);
    }
}