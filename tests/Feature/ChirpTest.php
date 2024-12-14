<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Chirp;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ChirpTest extends TestCase
{
    use RefreshDatabase;
    /**
     * A basic feature test example.
     */
   

    public function test_un_utilisateur_peut_creer_un_chirp()
    {
        // Simuler un utilisateur connecté
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => 'Mon premier chirp !'
        ]);

        // Vérifier que le chirp a été ajouté à la base de données
        $reponse->assertStatus(201); // Assurer que la méthode store retourne 201
        $this->assertDatabaseHas('chirps', [
            'message' => 'Mon premier chirp !',
            'user_id' => $utilisateur->id,
        ]);
    }

    public function test_un_chirp_ne_peut_pas_avoir_un_contenu_vide()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => ''
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_un_chirp_ne_peut_pas_depasse_255_caracteres()
    {
        $utilisateur = User::factory()->create();
        $this->actingAs($utilisateur);

        $reponse = $this->post('/chirps', [
            'message' => str_repeat('a', 256)
        ]);

        $reponse->assertSessionHasErrors(['message']);
    }

    public function test_les_chirps_sont_affiches_sur_la_page_d_accueil()
    {
        $chirps = Chirp::factory()->count(3)->create();
        $reponse = $this->get('/');
        foreach ($chirps as $chirp) {
            $reponse->assertSee($chirp->message);
        }
    }
    public function test_un_utilisateur_peut_modifier_son_chirp()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->put("/chirps/{$chirp->id}", [
 'message' => 'Chirp modifié'
 ]);
 $reponse->assertStatus(302);
 // Vérifie si le chirp existe dans la base de donnée.
 $this->assertDatabaseHas('chirps', [
 'id' => $chirp->id,
 'message' => 'Chirp modifié',
 ]);
}

public function test_un_utilisateur_peut_supprimer_son_chirp
()
{
 $utilisateur = User::factory()->create();
 $chirp = Chirp::factory()->create(['user_id' => $utilisateur->id]);
 $this->actingAs($utilisateur);
 $reponse = $this->delete("/chirps/{$chirp->id}");
 $reponse->assertStatus(302);
 $this->assertDatabaseMissing('chirps', [
    'id' => $chirp->id,
    ]);
   }
    public function test_utilisateur_2_ne_peut_pas_modifier_ni_supprimer_le_chirp_de_utilisateur_1()
    {
        // Créer deux utilisateurs et un chirp pour l'utilisateur 1
        $user1 = User::factory()->create();
        $user2 = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user1->id]);

        // Tenter de modifier le chirp avec utilisateur 2
        $this->actingAs($user2);
        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => 'Attempted change by user 2',
        ]);
        $response->assertStatus(403);

        // Tenter de supprimer le chirp avec utilisateur 2
        $response = $this->delete("/chirps/{$chirp->id}");
        $response->assertStatus(403);
    }
   
    public function test_contenu_vide_n_est_pas_accepte_lors_de_la_mise_a_jour()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => '',
        ]);

        $response->assertSessionHasErrors(['message']);
    }

    public function test_contenu_trop_long_n_est_pas_accepte_lors_de_la_mise_a_jour()
    {
        $user = User::factory()->create();
        $chirp = Chirp::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user);

        $response = $this->put("/chirps/{$chirp->id}", [
            'message' => str_repeat('a', 256),
        ]);

        $response->assertSessionHasErrors(['message']);
    }
}



